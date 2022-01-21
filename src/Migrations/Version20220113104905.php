<?php

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220113104905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bitbag_mollie_subscription ADD order_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription ADD CONSTRAINT FK_5E346303E415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id)');
        $this->addSql('CREATE INDEX IDX_5E346303E415FB15 ON bitbag_mollie_subscription (order_item_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bitbag_mollie_subscription DROP FOREIGN KEY FK_5E346303E415FB15');
        $this->addSql('DROP INDEX IDX_5E346303E415FB15 ON bitbag_mollie_subscription');
        $this->addSql('ALTER TABLE bitbag_mollie_subscription DROP order_item_id');
    }
}
