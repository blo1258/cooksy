<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250912104831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB6390FB88E14F');
        $this->addSql('DROP INDEX IDX_49BB6390FB88E14F ON recette');
        $this->addSql('ALTER TABLE recette CHANGE temp_preparation temp_preparation INT NOT NULL, CHANGE temp_cuisson temp_cuisson INT NOT NULL, CHANGE utilisateur_id auteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB639060BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_49BB639060BB6FE6 ON recette (auteur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB639060BB6FE6');
        $this->addSql('DROP INDEX IDX_49BB639060BB6FE6 ON recette');
        $this->addSql('ALTER TABLE recette CHANGE temp_preparation temp_preparation VARCHAR(255) NOT NULL, CHANGE temp_cuisson temp_cuisson VARCHAR(255) NOT NULL, CHANGE auteur_id utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB6390FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_49BB6390FB88E14F ON recette (utilisateur_id)');
    }
}
