<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305173135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fraise (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE symfony_demo_comment ADD CONSTRAINT FK_53AD8F834B89032C FOREIGN KEY (post_id) REFERENCES symfony_demo_post (id)');
        $this->addSql('ALTER TABLE symfony_demo_comment ADD CONSTRAINT FK_53AD8F83F675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id)');
        $this->addSql('ALTER TABLE symfony_demo_comment RENAME INDEX fk_53a TO IDX_53AD8F834B89032C');
        $this->addSql('ALTER TABLE symfony_demo_comment RENAME INDEX fk_53b TO IDX_53AD8F83F675F31B');
        $this->addSql('ALTER TABLE symfony_demo_post ADD CONSTRAINT FK_58A92E65F675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id)');
        $this->addSql('ALTER TABLE symfony_demo_post RENAME INDEX fk_58a TO IDX_58A92E65F675F31B');
        $this->addSql('ALTER TABLE symfony_demo_post_tag ADD CONSTRAINT FK_6ABC1CC44B89032C FOREIGN KEY (post_id) REFERENCES symfony_demo_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE symfony_demo_post_tag ADD CONSTRAINT FK_6ABC1CC4BAD26311 FOREIGN KEY (tag_id) REFERENCES symfony_demo_tag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6ABC1CC44B89032C ON symfony_demo_post_tag (post_id)');
        $this->addSql('ALTER TABLE symfony_demo_post_tag RENAME INDEX fk_6ac TO IDX_6ABC1CC4BAD26311');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4D5855405E237E06 ON symfony_demo_tag (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FB094A1F85E0677 ON symfony_demo_user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FB094A1E7927C74 ON symfony_demo_user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fraise');
        $this->addSql('ALTER TABLE symfony_demo_comment DROP FOREIGN KEY FK_53AD8F834B89032C');
        $this->addSql('ALTER TABLE symfony_demo_comment DROP FOREIGN KEY FK_53AD8F83F675F31B');
        $this->addSql('ALTER TABLE symfony_demo_comment RENAME INDEX idx_53ad8f834b89032c TO FK_53A');
        $this->addSql('ALTER TABLE symfony_demo_comment RENAME INDEX idx_53ad8f83f675f31b TO FK_53B');
        $this->addSql('ALTER TABLE symfony_demo_post DROP FOREIGN KEY FK_58A92E65F675F31B');
        $this->addSql('ALTER TABLE symfony_demo_post RENAME INDEX idx_58a92e65f675f31b TO FK_58A');
        $this->addSql('DROP INDEX UNIQ_8FB094A1F85E0677 ON symfony_demo_user');
        $this->addSql('DROP INDEX UNIQ_8FB094A1E7927C74 ON symfony_demo_user');
        $this->addSql('DROP INDEX UNIQ_4D5855405E237E06 ON symfony_demo_tag');
        $this->addSql('ALTER TABLE symfony_demo_post_tag DROP FOREIGN KEY FK_6ABC1CC44B89032C');
        $this->addSql('ALTER TABLE symfony_demo_post_tag DROP FOREIGN KEY FK_6ABC1CC4BAD26311');
        $this->addSql('DROP INDEX IDX_6ABC1CC44B89032C ON symfony_demo_post_tag');
        $this->addSql('ALTER TABLE symfony_demo_post_tag RENAME INDEX idx_6abc1cc4bad26311 TO FK_6AC');
    }
}
