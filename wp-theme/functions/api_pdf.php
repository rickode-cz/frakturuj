<?php

require_once dirname(__FILE__) . '/../vendor/autoload.php';

function generate_pdf($html, $filename = 'document.pdf', $paper = 'A4', $orientation = 'portrait') {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => $paper,
        'orientation' => $orientation,
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9,
        'default_font' => 'dejavusans',
        'allow_html_data_uri' => true
    ]);
    
    $mpdf->WriteHTML($html);
    $mpdf->Output($filename, 'D');
}

function rk_generate_invoice_pdf($request) {
    $invoice_id = $request['id'];
    $user_id = get_current_user_id()()();
    $invoice = get_post($invoice_id);
    
    if (!$invoice || $invoice->post_type !== 'invoice') {
        return new WP_Error('not_found', 'Faktura nenalezena', array('status' => 404));
    }

    // Check if invoice belongs to current user
    if ($invoice->post_author != $user_id) {
        return new WP_Error('unauthorized', 'Nemáte oprávnění generovat tuto fakturu', array('status' => 403));
    }

    // Prepare data
    $customer_id = get_field('customer', $invoice_id);
    $customer = get_post($customer_id);
    $items = json_decode(get_field('items', $invoice_id), true);

    // Use provided user_id for profile data
    $profile = array(
        'title' => get_option("rk_profile_{$user_id}_title"),
        'ico' => get_option("rk_profile_{$user_id}_ico"),
        'dic' => get_option("rk_profile_{$user_id}_dic"),
        'street' => get_option("rk_profile_{$user_id}_street"),
        'city' => get_option("rk_profile_{$user_id}_city"),
        'psc' => get_option("rk_profile_{$user_id}_psc"),
        'country' => get_option("rk_profile_{$user_id}_country"),
        'email' => get_option("rk_profile_{$user_id}_email"),
        'phone' => get_option("rk_profile_{$user_id}_phone"),
        'bank_account' => get_option("rk_profile_{$user_id}_bank_account"),
        'bank_code' => get_option("rk_profile_{$user_id}_bank_code")
    );

    // Calculate total before generating QR code
    $total = 0;
    $formatted_items = [];
    foreach ($items['items'] as $item) {
        $item_total = $item['quantity'] * $item['price_per_unit'];
        $total += $item_total;
        $formatted_items[] = [
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'units' => $item['units'],
            'price_per_unit' => $item['price_per_unit'],
            'total' => $item_total
        ];
    }

    // Generate QR code URL for payment
    $qr_params = array(
        'accountNumber' => $profile['bank_account'],
        'bankCode' => $profile['bank_code'],
        'amount' => number_format($total, 2, '.', ''),
        'currency' => 'CZK',
        'vs' => str_replace('-', '', $invoice->post_title),
        'message' => $profile['title']. ' ' . $invoice->post_title,
        'size' => 200
    );
    
    $qr_url = 'https://api.paylibo.com/paylibo/generator/czech/image?' . http_build_query($qr_params);
    
    $response = wp_remote_get($qr_url);
    
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $qr_image_data = wp_remote_retrieve_body($response);
        $qr_base64 = base64_encode($qr_image_data);
    }

    // Generate HTML
    $html = '
    <html lang="cs">
    <head>
        <meta charset="UTF-8">
        <style>
            body { 
                font-family: dejavusans, sans-serif;
                font-size: 9pt;
                line-height: 1.4;
                color: #333;
                margin: 0.5cm;
            }
            
            h1 {
                font-size: 24pt;
                font-weight: bold;
                color: #000;
                margin-bottom: 0.5cm;
            }
            
            h3 {
                font-size: 11pt;
                font-weight: bold;
                color: #000;
                margin: 0 0 10px 0;
                text-transform: uppercase;
            }
            
            .company-details {
                margin-bottom: 0.5cm;
            }
            
            .invoice-details {
                margin-bottom: 0.5cm;
                line-height: 1.6;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0.5cm;
            }
            
            th {
                background-color: #f5f5f5;
                font-weight: bold;
                text-align: left;
                padding: 8px;
                border-bottom: 2px solid #ddd;
            }
            
            td {
                padding: 8px;
                border-bottom: 1px solid #eee;
            }
            
            .amount-column {
                text-align: right;
            }
            
            .total {
                text-align: right;
                font-weight: bold;
                font-size: 12pt;
                margin-bottom: 0.5cm;
            }
            
            .qr-section {
                text-align: right;
            }
            
            .box {
                border: 1px solid #eee;
                padding: 15px;
                margin-bottom: 10px;
            }
            
            .payment-info {
                background-color: #f9f9f9;
                padding: 15px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Faktura č. ' . esc_html($invoice->post_title) . '</h1>
        </div>
        
        <div class="company-details">
            <div style="float: left; width: 45%;" class="box">
                <h3>Dodavatel</h3>
                <p>' . esc_html($profile['title']) . '<br>
                IČO: ' . esc_html($profile['ico']) . '<br>
                ' . (!empty($profile['dic']) ? 'DIČ: ' . esc_html($profile['dic']) : 'Neplátce DPH') . '<br>
                ' . esc_html($profile['street']) . '<br>
                ' . esc_html($profile['city']) . ' ' . esc_html($profile['psc']) . '<br>
                ' . esc_html($profile['country']) . '</p>
            </div>
            <div style="float: right; width: 45%;" class="box">
                <h3>Odběratel</h3>
                <p>' . esc_html($customer->post_title) . '<br>
                IČO: ' . esc_html(get_field('ico', $customer_id)) . '<br>
                ' . (!empty(get_field('dic', $customer_id)) ? 'DIČ: ' . esc_html(get_field('dic', $customer_id)) : 'Neplátce DPH') . '<br>
                ' . esc_html(get_field('street', $customer_id)) . '<br>
                ' . esc_html(get_field('city', $customer_id)) . ' ' . esc_html(get_field('psc', $customer_id)) . '<br>
                ' . esc_html(get_field('country', $customer_id)) . '</p>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="payment-info">
            <p>Datum vystavení: ' . get_the_date('d.m.Y', $invoice_id) . '<br>
            Datum splatnosti: ' . date('d.m.Y', strtotime(get_field('maturity', $invoice_id))) . '</p>
            
            <p>Bankovní účet: <strong>' . esc_html($profile['bank_account']) . '/' . esc_html($profile['bank_code']) . '</strong><br>
            Variabilní symbol: <strong>' . str_replace('-', '', $invoice->post_title) . '</strong><br>
            Způsob platby: <strong>Převodem</strong></p>
        </div>

        <table>
            <tr>
                <th style="width: 45%;">Položka</th>
                <th style="width: 20%;">Množství</th>
                <th style="width: 15%;" class="amount-column">Cena/j.</th>
                <th style="width: 20%;" class="amount-column">Celkem</th>
            </tr>';

    foreach ($formatted_items as $item) {
        $html .= '<tr>
            <td>' . esc_html($item['name']) . '</td>
            <td>' . esc_html($item['quantity']) . ' ' . esc_html($item['units']) . '</td>
            <td class="amount-column">' . number_format($item['price_per_unit'], 2, ',', ' ') . ' Kč</td>
            <td class="amount-column">' . number_format($item['total'], 2, ',', ' ') . ' Kč</td>
        </tr>';
    }

    $html .= '</table>
        <div class="total">
            <p style="font-size: 14pt;">Celkem k úhradě: ' . number_format($total, 2, ',', ' ') . ' Kč</p>
        </div>
        <div class="qr-section">';
    
    if ($qr_base64) {
        $html .= '<img src="data:image/png;base64,' . $qr_base64 . '" alt="QR Platba" width="200" height="200"/>';
    }
    
    $html .= '</div>
    </body>
    </html>';

    // Generate filename from profile title and invoice number
    $prefix = sanitize_title($profile['title']);
    $filename = $prefix . '-' . $invoice->post_title . '.pdf';

    // Generate PDF
    generate_pdf($html, $filename);
    exit;
}
