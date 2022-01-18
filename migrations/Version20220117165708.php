<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220117165708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment ADD episode_id INT NOT NULL, CHANGE author_id author_id INT NOT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id)');
        $this->addSql('CREATE INDEX IDX_9474526C362B62A0 ON comment (episode_id)');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDAF8697D13');
        $this->addSql('DROP INDEX IDX_DDAA1CDAF8697D13 ON episode');
        $this->addSql('ALTER TABLE episode DROP comment_id, CHANGE summary summary LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C362B62A0');
        $this->addSql('DROP INDEX IDX_9474526C362B62A0 ON comment');
        $this->addSql('ALTER TABLE comment DROP episode_id, CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE episode ADD comment_id INT DEFAULT NULL, CHANGE summary summary LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDAF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_DDAA1CDAF8697D13 ON episode (comment_id)');
    }
}
