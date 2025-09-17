<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917080440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recette_utilisateur (recette_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_E26F66FA89312FE9 (recette_id), INDEX IDX_E26F66FAFB88E14F (utilisateur_id), PRIMARY KEY(recette_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recette_utilisateur ADD CONSTRAINT FK_E26F66FA89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recette_utilisateur ADD CONSTRAINT FK_E26F66FAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recette ADD duree INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recette_utilisateur DROP FOREIGN KEY FK_E26F66FA89312FE9');
        $this->addSql('ALTER TABLE recette_utilisateur DROP FOREIGN KEY FK_E26F66FAFB88E14F');
        $this->addSql('DROP TABLE recette_utilisateur');
        $this->addSql('ALTER TABLE recette DROP duree');
    }
}
