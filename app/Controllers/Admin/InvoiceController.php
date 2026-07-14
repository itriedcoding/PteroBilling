<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Models\Invoice;

class InvoiceController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        $invoiceModel = new Invoice();
        $invoices = $invoiceModel->getAll();

        $html = $this->render('admin/invoices/index', [
            'user' => $request->getAttribute('user'),
            'invoices' => $invoices['data'],
            'total' => $invoices['total'],
            'csrf_token' => $this->session->get('csrf_token'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    private function render(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        $templatePath = __DIR__ . '/../../resources/views/' . $template . '.php';
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo "Template not found: $template";
        }
        return ob_get_clean();
    }
}
