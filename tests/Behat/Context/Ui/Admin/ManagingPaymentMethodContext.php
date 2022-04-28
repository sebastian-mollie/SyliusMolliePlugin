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
    /** @var CreatePageInterface */
    private $createPage;

    private string $mollieTestApiKey;

    private string $mollieProfileId;

    public const MOLLIE_TEST_API_KEY = 'MOLLIE_TEST_API_KEY';

    public const MOLLIE_PROFILE_KEY = 'MOLLIE_PROFILE_KEY';

    public function __construct(
        CreatePageInterface $createPage,
        string $mollieTestApiKey,
        string $mollieProfileId
    ) {
        $this->createPage = $createPage;
        $this->mollieTestApiKey = $mollieTestApiKey;
        $this->mollieProfileId = $mollieProfileId;
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
        if (self::MOLLIE_TEST_API_KEY === $apiKey) {
            $this->createPage->setApiKey($this->mollieTestApiKey);

            return;
        }

        $this->createPage->setApiKey($apiKey);
    }

    /**
     * @When I fill the Profile ID with :profileId
     */
    public function iConfigureProfileId(string $profileId): void
    {
        if (self::MOLLIE_PROFILE_KEY === $profileId) {
            $this->createPage->setProfileId($this->mollieProfileId);

            return;
        }

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
     * @Then I should be notified with error :message message
     */
    public function iShouldBeNotifiedWithErrorMessage(string $message): void
    {
        Assert::true($this->createPage->containsErrorWithMessage($message));
    }

    /**
     * @Then I should be notified with success :message message
     */
    public function iShouldBeNotifiedWithSuccessMessage(string $message): void
    {
        Assert::true($this->createPage->containsSuccessMessage($message));
    }

    /**
     * @Given I want to create a new Mollie recurring subscription
     */
    public function iWantToCreateANewMollieRecurringSubscription(): void
    {
        $this->createPage->open(['factory' => 'mollie_subscription']);
    }

    /**
     * @Given I can load payment methods
     */
    public function iCanLoadPaymentMethods(): void
    {
        $this->createPage->loadPaymentMethods();
    }

    /**
     * @Given I enable :paymentMethodName payment method
     */
    public function iEnablePaymentMethod(string $paymentMethodName): void
    {
        $this->createPage->enablePaymentMethod($paymentMethodName);
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
