<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225170215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE symfony_demo_comment ADD CONSTRAINT FK_53AD8F834B89032C FOREIGN KEY (post_id) REFERENCES symfony_demo_post (id)');
        $this->addSql('ALTER TABLE symfony_demo_comment ADD CONSTRAINT FK_53AD8F83F675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id)');
        $this->addSql('ALTER TABLE symfony_demo_post ADD CONSTRAINT FK_58A92E65F675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id)');
        $this->addSql('ALTER TABLE symfony_demo_post_tag ADD CONSTRAINT FK_6ABC1CC44B89032C FOREIGN KEY (post_id) REFERENCES symfony_demo_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE symfony_demo_post_tag ADD CONSTRAINT FK_6ABC1CC4BAD26311 FOREIGN KEY (tag_id) REFERENCES symfony_demo_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE symfony_demo_tag DROP INDEX UNIQ_4D5855405E237E06, ADD UNIQUE INDEX UNIQ_4D5855405E237E06 (name(250))');

        $this->addSql('ALTER TABLE symfony_demo_user DROP INDEX UNIQ_8FB094A1F85E0677, ADD UNIQUE INDEX UNIQ_8FB094A1E7927C74 (email(250))');
        $this->addSql('ALTER TABLE symfony_demo_user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE symfony_demo_comment DROP FOREIGN KEY FK_53AD8F834B89032C');
        $this->addSql('ALTER TABLE symfony_demo_comment DROP FOREIGN KEY FK_53AD8F83F675F31B');
        $this->addSql('ALTER TABLE symfony_demo_post DROP FOREIGN KEY FK_58A92E65F675F31B');
        $this->addSql('ALTER TABLE symfony_demo_post_tag DROP FOREIGN KEY FK_6ABC1CC44B89032C');
        $this->addSql('ALTER TABLE symfony_demo_post_tag DROP FOREIGN KEY FK_6ABC1CC4BAD26311');
        $this->addSql('ALTER TABLE symfony_demo_tag DROP INDEX UNIQ_4D5855405E237E06, ADD UNIQUE INDEX 4D585 (name(250))');
        $this->addSql('ALTER TABLE symfony_demo_user DROP INDEX UNIQ_8FB094A1F85E0677, ADD UNIQUE INDEX 8FB09 (username(250))');
        $this->addSql('ALTER TABLE symfony_demo_user DROP INDEX UNIQ_8FB094A1E7927C74, ADD UNIQUE INDEX 8FB07 (email(250))');
        $this->addSql('ALTER TABLE symfony_demo_user CHANGE roles roles TEXT NOT NULL');
    }
}
