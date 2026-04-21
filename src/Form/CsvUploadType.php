<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Simple upload form.
 *
 * Keeping validation constraints here (rather than in the controller)
 * is idiomatic Symfony and makes the form independently reusable.
 */
final class CsvUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('csvFile', FileType::class, [
            'label'       => 'Choose a CSV file',
            'required'    => true,
            'constraints' => [
                new NotBlank(message: 'Please select a file.'),
                new File([
                    'maxSize'          => '5M',
                    'mimeTypes'        => ['text/plain', 'text/csv', 'application/csv', 'application/octet-stream'],
                    'mimeTypesMessage' => 'Please upload a valid CSV file.',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
