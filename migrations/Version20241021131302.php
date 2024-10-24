<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021131302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE approval (post_id UUID NOT NULL, by_id UUID NOT NULL, changed_to VARCHAR(255) NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(post_id))');
        $this->addSql('CREATE INDEX IDX_16E0952BAAE72004 ON approval (by_id)');
        $this->addSql('COMMENT ON COLUMN approval.post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN approval.by_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN approval.approved_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE category (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN category.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE post_category (post_id UUID NOT NULL, category_id UUID NOT NULL, PRIMARY KEY(post_id, category_id))');
        $this->addSql('CREATE INDEX IDX_B9A190604B89032C ON post_category (post_id)');
        $this->addSql('CREATE INDEX IDX_B9A1906012469DE2 ON post_category (category_id)');
        $this->addSql('COMMENT ON COLUMN post_category.post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN post_category.category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE post_type (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN post_type.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE approval ADD CONSTRAINT FK_16E0952B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE approval ADD CONSTRAINT FK_16E0952BAAE72004 FOREIGN KEY (by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A190604B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A1906012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8d9d86650f');
        $this->addSql('DROP INDEX idx_5a8a6c8d9d86650f');
        $this->addSql('ALTER TABLE post ADD type_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD owner_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE post DROP user_id_id');
        $this->addSql('COMMENT ON COLUMN post.type_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN post.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC54C8C93 FOREIGN KEY (type_id) REFERENCES post_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DC54C8C93 ON post (type_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D7E3C61F9 ON post (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DC54C8C93');
        $this->addSql('ALTER TABLE approval DROP CONSTRAINT FK_16E0952B4B89032C');
        $this->addSql('ALTER TABLE approval DROP CONSTRAINT FK_16E0952BAAE72004');
        $this->addSql('ALTER TABLE post_category DROP CONSTRAINT FK_B9A190604B89032C');
        $this->addSql('ALTER TABLE post_category DROP CONSTRAINT FK_B9A1906012469DE2');
        $this->addSql('DROP TABLE approval');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE post_category');
        $this->addSql('DROP TABLE post_type');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D7E3C61F9');
        $this->addSql('DROP INDEX IDX_5A8A6C8DC54C8C93');
        $this->addSql('DROP INDEX IDX_5A8A6C8D7E3C61F9');
        $this->addSql('ALTER TABLE post ADD user_id_id UUID NOT NULL');
        $this->addSql('ALTER TABLE post DROP type_id');
        $this->addSql('ALTER TABLE post DROP owner_id');
        $this->addSql('COMMENT ON COLUMN post.user_id_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8d9d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5a8a6c8d9d86650f ON post (user_id_id)');
    }
}
