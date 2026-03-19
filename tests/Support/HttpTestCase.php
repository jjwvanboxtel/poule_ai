<?php declare(strict_types=1);

namespace Tests\Support;

use App\Infrastructure\Persistence\Pdo\ConnectionFactory;
use Database\Seeders\DevSeeder;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class HttpTestCase extends TestCase
{
    protected static string $baseUrl;
    protected static string $cookieFile;
    protected static mixed $serverProcess = null;
    protected static PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::bootstrapDatabase();
        self::startServer();
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }

        if (isset(self::$cookieFile) && file_exists(self::$cookieFile)) {
            unlink(self::$cookieFile);
        }

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::$cookieFile = tempnam(sys_get_temp_dir(), 'poule-cookie-') ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'poule-cookie.txt';
        $this->resetPredictionState();
    }

    protected function loginAsSeededParticipant(): void
    {
        $loginPage = $this->request('GET', '/login');
        $csrfToken = $this->extractCsrfToken($loginPage['body']);

        $response = $this->request('POST', '/login', [
            '_token' => $csrfToken,
            'email' => 'deelnemer@example.com',
            'password' => 'secret',
            'intended' => '',
        ]);

        self::assertSame(302, $response['status']);
        self::assertSame('/dashboard', $response['headers']['location'] ?? null);
    }

    /**
     * @param array<string, scalar|null> $data
     * @return array{status: int, body: string, headers: array<string, string>}
     */
    protected function request(string $method, string $path, array $data = []): array
    {
        $curl = curl_init();
        if ($curl === false) {
            throw new RuntimeException('cURL could not be initialized.');
        }

        $url = self::$baseUrl . $path;
        $headers = [];

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADERFUNCTION => static function ($ch, string $headerLine) use (&$headers): int {
                $length = strlen($headerLine);
                $headerLine = trim($headerLine);

                if ($headerLine !== '' && str_contains($headerLine, ':')) {
                    [$name, $value] = explode(':', $headerLine, 2);
                    $headers[strtolower(trim($name))] = trim($value);
                }

                return $length;
            },
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => self::$cookieFile,
            CURLOPT_COOKIEFILE => self::$cookieFile,
        ]);

        if (strtoupper($method) === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $body = curl_exec($curl);
        if (!is_string($body)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('HTTP request failed: ' . $error);
        }

        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return [
            'status' => $status,
            'body' => $body,
            'headers' => $headers,
        ];
    }

    protected function extractCsrfToken(string $html): string
    {
        if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $matches) !== 1) {
            throw new RuntimeException('CSRF token field was not found.');
        }

        return html_entity_decode($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function competitionIdBySlug(string $slug): int
    {
        $stmt = self::$pdo->prepare('SELECT id FROM competitions WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $value = $stmt->fetchColumn();

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @return list<int>
     */
    protected function matchIdsForCompetition(int $competitionId): array
    {
        $stmt = self::$pdo->prepare('SELECT id FROM matches WHERE competition_id = ? ORDER BY kickoff_at ASC, id ASC');
        $stmt->execute([$competitionId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    protected function bonusQuestionIdByPrompt(int $competitionId, string $prompt): int
    {
        $stmt = self::$pdo->prepare('SELECT id FROM bonus_questions WHERE competition_id = ? AND prompt = ? LIMIT 1');
        $stmt->execute([$competitionId, $prompt]);
        $value = $stmt->fetchColumn();

        return is_numeric($value) ? (int) $value : 0;
    }

    protected function entityIdByName(int $competitionId, string $name): int
    {
        $stmt = self::$pdo->prepare('SELECT id FROM catalog_entities WHERE competition_id = ? AND display_name = ? LIMIT 1');
        $stmt->execute([$competitionId, $name]);
        $value = $stmt->fetchColumn();

        return is_numeric($value) ? (int) $value : 0;
    }

    protected function firstKnockoutRoundId(int $competitionId): int
    {
        $stmt = self::$pdo->prepare(
            'SELECT id FROM knockout_rounds WHERE competition_id = ? ORDER BY round_order ASC LIMIT 1',
        );
        $stmt->execute([$competitionId]);
        $value = $stmt->fetchColumn();

        return is_numeric($value) ? (int) $value : 0;
    }

    private static function bootstrapDatabase(): void
    {
        self::loadEnvironment();
        /** @var array{host: string, port: int, name: string, user: string, password: string, charset: string, options?: array<int, mixed>} $config */
        $config = require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
        self::$pdo = ConnectionFactory::fromConfig($config);

        $migrationFiles = glob(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($migrationFiles);

        foreach ($migrationFiles as $migrationFile) {
            $migration = require $migrationFile;
            $migration->up(self::$pdo);
        }

        $seeder = new DevSeeder(self::$pdo);
        $seeder->run();
    }

    private static function loadEnvironment(): void
    {
        $envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    private static function startServer(): void
    {
        $port = random_int(8800, 8999);
        self::$baseUrl = 'http://127.0.0.1:' . $port;
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $command = PHP_BINARY . ' -S 127.0.0.1:' . $port . ' -t public';
        $cwd = dirname(__DIR__, 2);

        $process = proc_open($command, $descriptorSpec, $pipes, $cwd);
        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start PHP built-in server.');
        }

        self::$serverProcess = $process;

        $started = false;
        for ($attempt = 0; $attempt < 20; $attempt++) {
            usleep(250_000);
            $body = @file_get_contents(self::$baseUrl . '/login');
            if (is_string($body)) {
                $started = true;
                break;
            }
        }

        if (!$started) {
            proc_terminate($process);
            throw new RuntimeException('PHP built-in server did not become ready in time.');
        }
    }

    private function resetPredictionState(): void
    {
        self::$pdo->exec('DELETE FROM bonus_answers');
        self::$pdo->exec('DELETE FROM knockout_round_predictions');
        self::$pdo->exec('DELETE FROM match_predictions');
        self::$pdo->exec('DELETE FROM prediction_submissions');
        self::$pdo->exec("DELETE FROM competition_participants WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'e2e+%@example.com')");
        self::$pdo->exec("DELETE FROM users WHERE email LIKE 'e2e+%@example.com'");

        $seeder = new DevSeeder(self::$pdo);
        $seeder->run();

        if (file_exists(self::$cookieFile)) {
            file_put_contents(self::$cookieFile, '');
        }
    }
}
