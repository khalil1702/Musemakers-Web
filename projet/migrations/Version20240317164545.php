<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240317164545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id_reservation INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, id_exposition INT DEFAULT NULL, date_reser DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, tickets_number INT DEFAULT NULL, accessByAdmin INT DEFAULT NULL, INDEX id_user (id_user), INDEX id_exposition (id_exposition), PRIMARY KEY(id_reservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849556B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519BB7DC4 FOREIGN KEY (id_exposition) REFERENCES exposition (id_exposition)');
        $this->addSql('ALTER TABLE atelier CHANGE id_cours id_cours INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire CHANGE idRec idRec INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cours CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE idU idU INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849556B3CA4B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519BB7DC4');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE atelier CHANGE id_cours id_cours INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire CHANGE idRec idRec INT NOT NULL');
        $this->addSql('ALTER TABLE cours CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE idU idU INT NOT NULL');
    }
}
