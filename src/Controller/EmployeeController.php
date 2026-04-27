<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\InvalidCsvException;
use App\Exception\UnparsableDateException;
use App\Form\CsvUploadType;
use App\Service\CsvParserService;
use App\Service\EmployeePairFinderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the main employee CSV upload and pair-finding workflow.
 *
 * Depends on CsvParserService to parse uploaded CSV files into
 * EmployeeProject records, and EmployeePairFinderService to determine
 * which pair of employees worked together the longest across all projects.
 */
final class EmployeeController extends AbstractController
{
    /**
     * @param CsvParserService         $csvParser  Parses raw CSV data into EmployeeProject value objects
     * @param EmployeePairFinderService $pairFinder Finds the employee pair with the longest combined overlap
     */
    public function __construct(
        private readonly CsvParserService         $csvParser,
        private readonly EmployeePairFinderService $pairFinder,
    ) {}

    /**
     * Renders the upload form and processes a submitted CSV file.
     *
     * On a valid POST request the uploaded file is parsed, and the pair of
     * employees who worked together the longest across all projects is
     * calculated and passed to the template. Any CSV- or date-parsing errors
     * are caught and surfaced to the user as a human-readable message.
     */
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(CsvUploadType::class);
        $form->handleRequest($request);

        $result       = null;
        $errorMessage = null;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
            $uploadedFile = $form->get('csvFile')->getData();

            try {
                // Parse the CSV file into an array of EmployeeProject records.
                $records = $this->csvParser->parse($uploadedFile->getPathname());

                // Determine the pair of employees with the maximum total overlap.
                $result = $this->pairFinder->findTopPair($records);

                if ($result === null) {
                    // No two employees shared an overlapping period on any project.
                    $errorMessage = 'No overlapping pairs found. Make sure at least two employees worked on the same project during overlapping periods.';
                }
            } catch (InvalidCsvException $e) {
                // Malformed CSV structure (missing columns, wrong format, etc.).
                $errorMessage = 'CSV error: ' . $e->getMessage();
            } catch (UnparsableDateException $e) {
                // A date value in the CSV could not be parsed by any registered strategy.
                $errorMessage = 'Date parsing error: ' . $e->getMessage();
            }
        }

        return $this->render('employee/index.html.twig', [
            'form'         => $form,
            'result'       => $result,
            'errorMessage' => $errorMessage,
        ]);
    }
}
