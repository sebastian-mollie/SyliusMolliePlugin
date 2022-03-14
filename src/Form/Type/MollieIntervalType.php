<?php
declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Form\Type;

use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MollieIntervalType extends AbstractType
{
    private DataTransformerInterface $transformer;

    public function __construct(DataTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', NumberType::class);
        $builder->add('step', ChoiceType::class, [
            'choices' => array_combine(
                MollieSubscriptionConfigurationInterface::SUPPORTED_INTERVAL_STEPS,
                MollieSubscriptionConfigurationInterface::SUPPORTED_INTERVAL_STEPS
            ),
            'choice_label' => static function (string $value) {
                return sprintf('bitbag_sylius_mollie_plugin.form.product_variant.interval_configuration.steps.%s', $value);
            }
        ]);
        $builder->addViewTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'label_format' => 'bitbag_sylius_mollie_plugin.form.product_variant.interval_configuration.%name%',
        ]);
    }
}
