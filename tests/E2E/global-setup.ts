import { execSync } from 'node:child_process';

export default async function globalSetup(): Promise<void> {
  execSync('php bin/migrate.php', { stdio: 'inherit' });
  execSync('php bin/seed.php', { stdio: 'inherit' });
}
