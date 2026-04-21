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

final class EmployeeController extends AbstractController
{
    public function __construct(
        private readonly CsvParserService         $csvParser,
        private readonly EmployeePairFinderService $pairFinder,
    ) {}

    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(CsvUploadType::class);
        $form->handleRequest($request);

        $result     = null;
        $errorMessage = null;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
            $uploadedFile = $form->get('csvFile')->getData();

            try {
                $records = $this->csvParser->parse($uploadedFile->getPathname());
                $result  = $this->pairFinder->findTopPair($records);

                if ($result === null) {
                    $errorMessage = 'No overlapping pairs found. Make sure at least two employees worked on the same project during overlapping periods.';
                }
            } catch (InvalidCsvException $e) {
                $errorMessage = 'CSV error: ' . $e->getMessage();
            } catch (UnparsableDateException $e) {
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
