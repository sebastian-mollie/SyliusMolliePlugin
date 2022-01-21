<?php

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112141532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bitbag_mollie_subscription_orders (subscription_id INT NOT NULL, order_id INT NOT NULL, INDEX IDX_DCE71BD39A1887DC (subscription_id), INDEX IDX_DCE71BD38D9F6D38 (order_id), PRIMARY KEY(subscription_id, order_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription_orders ADD CONSTRAINT FK_DCE71BD39A1887DC FOREIGN KEY (subscription_id) REFERENCES bitbag_mollie_subscription (id)');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription_orders ADD CONSTRAINT FK_DCE71BD38D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bitbag_mollie_subscription_orders');
    }
}
