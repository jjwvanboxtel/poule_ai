<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class CsvImportRoutesTest extends HttpTestCase
{
    public function testAdminCanViewImportPage(): void
    {
        $this->loginAsSeededAdmin();

        $response = $this->request('GET', '/admin/imports/entities');

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('importeren', strtolower($response['body']));
        self::assertStringContainsString('CSV', $response['body']);
    }

    public function testParticipantCannotAccessImportPage(): void
    {
        $this->loginAsSeededParticipant();

        $response = $this->request('GET', '/admin/imports/entities');

        self::assertSame(403, $response['status']);
    }

    public function testUnauthenticatedUserCannotAccessImportPage(): void
    {
        $response = $this->request('GET', '/admin/imports/entities');

        self::assertSame(302, $response['status']);
        self::assertStringContainsString('/login', $response['headers']['location'] ?? '');
    }

    public function testImportPageContainsCsvFormatHint(): void
    {
        $this->loginAsSeededAdmin();

        $response = $this->request('GET', '/admin/imports/entities');

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('display_name', $response['body']);
        self::assertStringContainsString('entity_type', $response['body']);
    }
}
