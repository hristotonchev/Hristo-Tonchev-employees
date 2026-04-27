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
        // FileType renders as <input type="file"> in the browser.
        $builder->add('csvFile', FileType::class, [
            'label'       => 'Choose a CSV file',
            'required'    => true,
            'constraints' => [
                // Ensures the user actually selected a file before submitting.
                new NotBlank(message: 'Please select a file.'),
                new File([
                    // Reject files over 5MB before any parsing happens.
                    'maxSize'          => '5M',
                    // Different browsers/OS send CSV files with different MIME types,
                    // so we whitelist all common variants to avoid false rejections.
                    'mimeTypes'        => ['text/plain', 'text/csv', 'application/csv', 'application/octet-stream'],
                    'mimeTypesMessage' => 'Please upload a valid CSV file.',
                ]),
            ],
        ]);
    }

    // No custom options needed — satisfies the interface contract.
    // A data_class would go here if the form were bound to an entity.
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
