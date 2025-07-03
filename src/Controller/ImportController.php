<?php

namespace App\Controller;

use App\DTOs\PaginationDTO;
use App\Form\ExcelUploadTypeForm;
use App\Service\ExcelImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImportController extends AbstractController
{
    public function __construct(private ExcelImportService $importService) {}

    #[Route('/import', name: 'app_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response|JsonResponse
    {
        $form = $this->createForm(ExcelUploadTypeForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file) {
                $filename = $this->importService->handleUpload($file);
                return $this->json([
                    'message' => 'File uploaded and processing started.',
                    'filename' => $filename,
                ]);
            }
        }

        $page = 1;
        $limit = 20;

        $total = $this->importService->countAll();
        $data = $this->importService->fetchPaginated($limit, ($page - 1) * $limit);

        $paginationDto = new PaginationDto(
            page: $page,
            limit: $limit,
            total: $total,
            data: $data
        );

        return $this->render('import/index.html.twig', [
            'form' => $form->createView(),
            'data' => $paginationDto->data,
            'page' => $paginationDto->page,
            'limit' => $paginationDto->limit,
            'total' => $paginationDto->total,
        ]);
    }

    #[Route('/import/status/{filename}', name: 'import_status_check')]
    public function checkStatus(string $filename): JsonResponse
    {
        $status = $this->importService->findImportStatusByFilename($filename);

        return $this->json([
            'completed' => $status?->isCompleted() ?? false,
            'failed' => $status?->isFailed() ?? false,
            'error' => $status?->getErrorMessage(),
        ]);
    }

    #[Route('/import/table', name: 'app_import_table')]
    public function table(Request $request): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $total = $this->importService->countAll();
        $data = $this->importService->fetchPaginated($limit, $offset);

        return $this->render('import/_table.html.twig', [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/import/progress/{filename}', name: 'import_progress_check')]
    public function progress(string $filename): JsonResponse
    {
        $status = $this->importService->findImportStatusByFilename($filename);

        if (!$status) {
            return $this->json(['progress' => 0]);
        }

        $progress = 0;
        if ($status->getTotalRows() > 0) {
            $progress = min(100, (int)(($status->getProcessedRows() / $status->getTotalRows()) * 100));
        }

        return $this->json(['progress' => $progress]);
    }
}
