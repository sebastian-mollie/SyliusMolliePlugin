<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Form\Type;

use BitBag\SyliusMolliePlugin\Documentation\DocumentationLinksInterface;
use BitBag\SyliusMolliePlugin\Payments\PaymentTerms\Options;
use BitBag\SyliusMolliePlugin\Validator\Constraints\LiveApiKeyIsNotBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

final class MollieGatewayConfigurationType extends AbstractType
{
    public const API_KEY_LIVE = 'api_key_live';

    public const API_KEY_TEST = 'api_key_test';

    /** @var DocumentationLinksInterface */
    private $documentationLinks;

    public function __construct(DocumentationLinksInterface $documentationLinks)
    {
        $this->documentationLinks = $documentationLinks;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'label' => 'bitbag_sylius_mollie_plugin.ui.environment',
                'choices' => [
                    'bitbag_sylius_mollie_plugin.ui.api_key_choice_test' => null,
                    'bitbag_sylius_mollie_plugin.ui.api_key_choice_live' => true,
                ],
            ])
            ->add('profile_id', TextType::class, [
                'label' => $this->documentationLinks->getProfileIdDoc(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'bitbag_sylius_mollie_plugin.profile_id.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add(self::API_KEY_TEST, PasswordType::class, [
                'always_empty' => false,
                'label' => $this->documentationLinks->getApiKeyDoc(),
                'help' => ' ',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bitbag_sylius_mollie_plugin.api_key.not_blank',
                        'groups' => ['sylius'],
                    ]),
                    new Regex([
                        'message' => 'bitbag_sylius_mollie_plugin.api_key.invalid_test',
                        'groups' => ['sylius'],
                        'pattern' => '/^(test)_\w{0,}$/',
                    ]),
                    new Length([
                        'minMessage' => 'bitbag_sylius_mollie_plugin.api_key.min_length',
                        'groups' => ['sylius'],
                        'min' => 35,
                    ]),
                ],
            ])
            ->add(self::API_KEY_LIVE, PasswordType::class, [
                'always_empty' => false,
                'required' => true,
                'label' => 'bitbag_sylius_mollie_plugin.ui.api_key_live',
                'constraints' => [
                    new Regex([
                        'message' => 'bitbag_sylius_mollie_plugin.api_key.invalid_live',
                        'groups' => ['sylius'],
                        'pattern' => '/^(live)_\w{0,}$/',
                    ]),
                    new Length([
                        'minMessage' => 'bitbag_sylius_mollie_plugin.api_key.min_length',
                        'groups' => ['sylius'],
                        'min' => 35,
                    ]),
                ],
            ])
            ->add('abandoned_email_enabled', CheckboxType::class, [
                'label' => 'bitbag_sylius_mollie_plugin.ui.abandoned_email_enabled',
                'help' => 'bitbag_sylius_mollie_plugin.ui.abandoned_description',
            ])
            ->add('abandoned_hours', ChoiceType::class, [
                'label' => 'bitbag_sylius_mollie_plugin.ui.abandoned_hours',
                'choices' => array_combine(
                    range(1, 200, 1),
                    range(1, 200, 1)
                ),
            ])
            ->add('loggerLevel', ChoiceType::class, [
                'label' => 'bitbag_sylius_mollie_plugin.ui.debug_level_log',
                'choices' => Options::getDebugLevels(),
            ])
            ->add('components', CheckboxType::class, [
                'label' => 'bitbag_sylius_mollie_plugin.ui.enable_components',
                'attr' => ['class' => 'bitbag-mollie-components'],
                'help' => $this->documentationLinks->getMollieComponentsDoc(),
                'help_html' => true,
            ])
            ->add('single_click_enabled', CheckboxType::class, [
                'label' => 'bitbag_sylius_mollie_plugin.ui.single_click_enabled',
                'attr' => ['class' => 'bitbag-single-click-payment'],
                'help' => $this->documentationLinks->getSingleClickDoc(),
                'help_html' => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['components']) && true === $data['components']) {
                    $data['single_click_enabled'] = false;
                }

                $data['payum.http_client'] = '@bitbag_sylius_mollie_plugin.mollie_api_client';

                $event->setData($data);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'bitbag_mollie_gateway_configuration_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('constraints', [
            new LiveApiKeyIsNotBlank(['field' => self::API_KEY_LIVE], ['sylius']),
        ]);

    }
}
