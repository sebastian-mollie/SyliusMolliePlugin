@managing_mollie_payment_method
Feature: Mollie payment method validation
    In order to avoid making mistakes when managing a payment method
    As an Administrator
    I want to be prevented from adding it without specifying required fields

    Background:
        Given the store operates on a channel named "Web-RUB" in "RUB" currency
        And the store has a payment method "Offline" with a code "offline"
        And I am logged in as an administrator

    @ui
    Scenario: Trying to add a new mollie payment method without specifying required configuration
        Given I want to create a new Mollie payment method
        When I name it "Mollie" in "English (United States)"
        And I add it
        Then I should be notified that "API key" fields cannot be blank

    @ui
    Scenario: Trying to add a new mollie payment method without the correct api key
        Given I want to create a new Mollie payment method
        When I name it "Mollie" in "English (United States)"
        And I fill the Profile ID with "pfl_6nBFHppqBs"
        And I fill the API key with "tttt_jdqkCbp55GRnfb9nFRz7R555pJMW4"
        And I add it
        Then I should be notified that 'Invalid API key. An API key must start with "test_".'
        And I should be notified that "API key must be at least 35 characters long."
