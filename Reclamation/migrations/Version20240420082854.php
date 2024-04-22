<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240420082854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE atelier DROP FOREIGN KEY atelier_ibfk_1');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY avis_ibfk_1');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY avis_ibfk_2');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY cours_ibfk_1');
        $this->addSql('DROP TABLE atelier');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE oeuvre');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY commentaire_ibfk_1');
        $this->addSql('DROP INDEX idrec ON commentaire');
        $this->addSql('CREATE INDEX IDX_67F068BC7D00914B ON commentaire (idrec)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT commentaire_ibfk_1 FOREIGN KEY (idRec) REFERENCES reclamation (idRec)');
        $this->addSql('ALTER TABLE exposition CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE date_debut date_debut DATE NOT NULL, CHANGE date_fin date_fin DATE NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE theme theme VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL, CHANGE heure_debut heure_debut DATE NOT NULL, CHANGE heure_fin heure_fin DATE NOT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY reclamation_ibfk_1');
        $this->addSql('ALTER TABLE reclamation CHANGE descriRec descriRec TINYTEXT NOT NULL');
        $this->addSql('DROP INDEX idu ON reclamation');
        $this->addSql('CREATE INDEX IDX_CE606404A2D72265 ON reclamation (idU)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT reclamation_ibfk_1 FOREIGN KEY (idU) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519BB7DC4');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849556B3CA4B');
        $this->addSql('ALTER TABLE reservation CHANGE date_reser date_reser DATETIME NOT NULL, CHANGE tickets_number tickets_number INT NOT NULL, CHANGE accessByAdmin accessByAdmin INT NOT NULL');
        $this->addSql('DROP INDEX id_user ON reservation');
        $this->addSql('CREATE INDEX IDX_42C849556B3CA4B ON reservation (id_user)');
        $this->addSql('DROP INDEX id_exposition ON reservation');
        $this->addSql('CREATE INDEX IDX_42C8495519BB7DC4 ON reservation (id_exposition)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519BB7DC4 FOREIGN KEY (id_exposition) REFERENCES exposition (id_exposition)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849556B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) NOT NULL, CHANGE num_tel num_tel INT NOT NULL, CHANGE date_de_naissance date_de_naissance DATE NOT NULL, CHANGE cartepro cartepro VARCHAR(255) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE sexe sexe VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE atelier (id_atelier INT AUTO_INCREMENT NOT NULL, id_cours INT DEFAULT NULL, dateDebut_atelier DATE NOT NULL, dateFin_atelier DATE NOT NULL, lien_atelier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX id_cours (id_cours), PRIMARY KEY(id_atelier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE avis (id_avis INT AUTO_INCREMENT NOT NULL, id_oeuvre INT DEFAULT NULL, id_user INT DEFAULT NULL, commentaire VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_experience DATE DEFAULT NULL, note INT DEFAULT NULL, likes INT DEFAULT NULL, dislikes INT DEFAULT NULL, favoris TINYINT(1) DEFAULT NULL, INDEX id_oeuvre (id_oeuvre), INDEX id_user (id_user), PRIMARY KEY(id_avis)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cours (id_cours INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, titre_cours VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, descri_cours TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateDebut_cours DATE NOT NULL, dateFin_cours DATE NOT NULL, INDEX id_user (id_user), PRIMARY KEY(id_cours)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE oeuvre (id_oeuvre INT AUTO_INCREMENT NOT NULL, nom_oeuvre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, categorie_oeuvre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prix_oeuvre DOUBLE PRECISION NOT NULL, datecreation DATE NOT NULL, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id_oeuvre)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE atelier ADD CONSTRAINT atelier_ibfk_1 FOREIGN KEY (id_cours) REFERENCES cours (id_cours)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT avis_ibfk_1 FOREIGN KEY (id_oeuvre) REFERENCES oeuvre (id_oeuvre)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT avis_ibfk_2 FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT cours_ibfk_1 FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7D00914B');
        $this->addSql('DROP INDEX idx_67f068bc7d00914b ON commentaire');
        $this->addSql('CREATE INDEX idRec ON commentaire (idRec)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7D00914B FOREIGN KEY (idrec) REFERENCES reclamation (idRec)');
        $this->addSql('ALTER TABLE exposition CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE theme theme VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE heure_debut heure_debut TIME DEFAULT NULL, CHANGE heure_fin heure_fin TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A2D72265');
        $this->addSql('ALTER TABLE reclamation CHANGE descriRec descriRec TEXT NOT NULL');
        $this->addSql('DROP INDEX idx_ce606404a2d72265 ON reclamation');
        $this->addSql('CREATE INDEX idU ON reclamation (idU)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A2D72265 FOREIGN KEY (idU) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849556B3CA4B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519BB7DC4');
        $this->addSql('ALTER TABLE reservation CHANGE tickets_number tickets_number INT DEFAULT NULL, CHANGE accessByAdmin accessByAdmin INT DEFAULT NULL, CHANGE date_reser date_reser DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX idx_42c849556b3ca4b ON reservation');
        $this->addSql('CREATE INDEX id_user ON reservation (id_user)');
        $this->addSql('DROP INDEX idx_42c8495519bb7dc4 ON reservation');
        $this->addSql('CREATE INDEX id_exposition ON reservation (id_exposition)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849556B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519BB7DC4 FOREIGN KEY (id_exposition) REFERENCES exposition (id_exposition)');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE num_tel num_tel INT DEFAULT NULL, CHANGE date_de_naissance date_de_naissance DATE DEFAULT NULL, CHANGE cartepro cartepro VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE sexe sexe VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
    }
}
