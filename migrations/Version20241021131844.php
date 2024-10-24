<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021131844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE approval DROP CONSTRAINT approval_pkey');
        $this->addSql('ALTER TABLE approval ADD id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN approval.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16E0952B4B89032C ON approval (post_id)');
        $this->addSql('ALTER TABLE approval ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_16E0952B4B89032C');
        $this->addSql('DROP INDEX approval_pkey');
        $this->addSql('ALTER TABLE approval DROP id');
        $this->addSql('ALTER TABLE approval ADD PRIMARY KEY (post_id)');
    }
}
