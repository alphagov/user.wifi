<?php


class pdf
{
    public $filename;
    public $subject;
    public $message;
    public $landscape;
    public $password;

    public function populate_newsite($site)
    {
        $this->message = file_get_contents($configuration['pdf-contents']['newsite-file']);
        $this->message = str_replace("%ORG%", $site->org_name, $this->message);
        $this->message = str_replace("%RADKEY%", $site->radkey, $this->message);
        $this->message = str_replace("%DESCRIPTION%", $site->name, $this->message);
        $this->filename = $site->org_name . "-" . $site->name;
        $this->filename = preg_replace("/[^a-zA-Z0-9]/", "_", $this->filename);
        $this->filename .= ".pdf";
        $this->filename = $configuration['pdftemp-path'] . $this->filename;
        $this->subject = $configuration['email-messages']['newsite-subject'];
    }

    public function populate_logrequest($org_admin)
    {
        $this->filename = date("Ymd") . $org_admin->org_name . "-" . $org_admin->name .
            "-Logs";
        $this->filename = preg_replace("/[^a-zA-Z0-9]/", "_", $this->filename);
        $this->filename .= ".pdf";
        $this->filename = $configuration['pdftemp-path'] . $this->filename;
        $this->subject = "Generated on: " . date("d-m-Y") . " Requestor: " . $org_admin->
            name;
    }

    public function generatepdf($handle = null)
    {
        // Generate PDF with the site details
        // Encrypts the file then returns the password
        $un_filename = $this->filename . "-unencrypted";
        if ($self->landscape)
            $pdf = new FPDF("L");
        else
            $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Courier', 'B', 16);
        $pdf->Cell(40, 10, 'user.wifi Service');
        $pdf->Ln(20);
        $pdf->Cell(80, 10, $subject);
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 12);
        // Write Body

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $message) as $line) {
            if ($line == "%TABLE%")
                $this->pdfsqltable($pdf, $handle);
            else
                $pdf->Write(5, $line . "\n");
        }
        $pdf->Output($un_filename);
        $this->encryptpdf($un_filename);
    }

    private function encryptpdf($filename)
    {
        $self->password = generate_random_pdf_password();
        exec("/usr/bin/qpdf --encrypt " . $self->password . " - 256 -- " . $filename .
            " " . $self->filename);
        unlink($filename);
    }

    private function pdfsqltable($pdf, $handle)
    {
        global $dblink;
        $handle->execute();
        $result = $handle->fetchAll(\PDO::FETCH_NUM);
        $totalrows = 0;
        $w = array(
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0);

        foreach ($result as $row[$totalrows]) {
            $column = 0;

            while (isset($row[$totalrows][$column])) {
                $collength = strlen($row[$totalrows][$column]);
                if ($w[$column] < $collength)
                    $w[$column] = $collength * 4;
                $column++;
            }
            $totalrows++;
        }
        for ($rownum = 0; $rownum <= $totalrows; $rownum++) {
            $column = 0;

            while (isset($row[$rownum][$column])) {
                $pdf->Cell($w[$column], 6, $row[$rownum][$column], 1, 0, 'C');
                $column++;
            }
            $pdf->Ln();
        }
    }

    private function generate_random_pdf_password()
    {
        global $configuration;
        $length = $configuration['pdf-password']['length'];
        $pattern = $configuration['pdf-password']['regex'];
        $pass = preg_replace($pattern, "", base64_encode(strong_random_bytes($length * 4)));
        return substr($pass, 0, $length);
    }
}

?>
