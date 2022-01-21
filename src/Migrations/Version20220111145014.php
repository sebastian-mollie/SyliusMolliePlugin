<?php

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111145014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bitbag_mollie_subscription_product');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription DROP FOREIGN KEY FK_5E346303A76ED395');
        $this->addSql('DROP INDEX IDX_5E346303A76ED395 ON bitbag_mollie_subscription');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription DROP user_id');
        $this->addSql('CREATE INDEX IDX_5E3463039395C3F3 ON bitbag_mollie_subscription (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bitbag_mollie_subscription_product (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, product_id INT DEFAULT NULL, product_amount INT NOT NULL, INDEX IDX_8B1B805A4584665A (product_id), INDEX IDX_8B1B805A9A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription_product ADD CONSTRAINT FK_8B1B805A4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription_product ADD CONSTRAINT FK_8B1B805A9A1887DC FOREIGN KEY (subscription_id) REFERENCES bitbag_mollie_subscription (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX IDX_5E3463039395C3F3 ON bitbag_mollie_subscription');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription ADD CONSTRAINT FK_5E346303A76ED395 FOREIGN KEY (user_id) REFERENCES sylius_shop_user (id)');
        $this->addSql('CREATE INDEX IDX_5E346303A76ED395 ON bitbag_mollie_subscription (user_id)');
    }
}
