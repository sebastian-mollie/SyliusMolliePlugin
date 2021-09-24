<?php

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210923142201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bitbag_mollie_configuration_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_369B057A2C2AC5D3 (translatable_id), UNIQUE INDEX bitbag_mollie_configuration_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bitbag_mollie_configuration_translation ADD CONSTRAINT FK_369B057A2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES bitbag_mollie_configuration (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bitbag_mollie_configuration_translation');
    }
}
