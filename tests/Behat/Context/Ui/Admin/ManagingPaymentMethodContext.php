<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusMolliePlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\BitBag\SyliusMolliePlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingPaymentMethodContext implements Context
{
    /**
     * @var CreatePageInterface
     */
    private $createPage;

    /**
     * @param CreatePageInterface $createPage
     */
    public function __construct(CreatePageInterface $createPage)
    {
        $this->createPage = $createPage;
    }

    /**
     * @Given I want to create a new Mollie payment method
     */
    public function iWantToCreateANewMolliePaymentMethod(): void
    {
        $this->createPage->open(['factory' => 'mollie']);
    }

    /**
     * @When I fill the API key with :apiKey
     */
    public function iConfigureItWithTestMollieCredentials(string $apiKey): void
    {
        $this->createPage->setApiKey($apiKey);
    }

    /**
     * @When I fill the Profile ID with :profileId
     */
    public function iConfigureProfileId(string $profileId): void
    {
        $this->createPage->setProfileId($profileId);
    }

    /**
     * @Then I should be notified that :fields fields cannot be blank
     */
    public function iShouldBeNotifiedThatCannotBeBlank(string $fields): void
    {
        $fields = explode(',', $fields);

        foreach ($fields as $field) {
            Assert::true($this->createPage->containsErrorWithMessage(sprintf(
                '%s cannot be blank.',
                trim($field)
            )));
        }
    }

    /**
     * @Then I should be notified that :message
     */
    public function iShouldBeNotifiedThat(string $message): void
    {
        Assert::true($this->createPage->containsErrorWithMessage($message));
    }

    /**
     * @Given I want to create a new Mollie recurring subscription
     */
    public function iWantToCreateANewMollieRecurringSubscription(): void
    {
        $this->createPage->open(['factory' => 'mollie_subscription']);
    }

    /**
     * @When I fill the times with :times
     */
    public function iFillTheTimesWith(int $times): void
    {
        $this->createPage->setTimes($times);
    }

    /**
     * @When I fill the interval with :interval
     */
    public function iFillTheIntervalWith(string $interval): void
    {
        $this->createPage->setInterval($interval);
    }
}
