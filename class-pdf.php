<?php

class PDF
{
    public $filename;
    public $filepath;
    public $subject;
    public $message;
    public $landscape;
    public $password;

    public function populateNewSite($site)
    {
        $config = config::getInstance();
        $this->message = file_get_contents($config->values['pdf-contents']['newsite-file']);
        $this->message = str_replace("%ORG%", $site->org_name, $this->message);
        $this->message = str_replace("%RADKEY%", $site->radkey, $this->message);
        $this->message = str_replace("%DESCRIPTION%", $site->name, $this->message);
        $this->filename = $site->org_name . "-" . $site->name;
        $this->filename = preg_replace("/[^a-zA-Z0-9]/", "_", $this->filename);
        $this->filename .= ".pdf";
        $this->filepath = $config->values['pdftemp-path'] . $this->filename;
        $this->subject = "New Site";
    }

    public function populateLogrequest($org_admin)
    {
        $config = config::getInstance();
        $this->filename = date("Ymd") . $org_admin->org_name . "-" . $org_admin->name .
            "-Logs";
        $this->filename = preg_replace("/[^a-zA-Z0-9]/", "_", $this->filename);
        $this->filename .= ".pdf";
        $this->filepath = $config->values['pdftemp-path'] . $this->filename;
        $this->subject = "Generated on: " . date("d-m-Y") . " Requestor: " . $org_admin->
            name;
        $this->message = file_get_contents($config->values['pdf-contents']['logrequest-file']);
    }

    public function generatePDF($table = null)
    {
        // Generate PDF with the site details
        // Encrypts the file then returns the password
        $un_filename = $this->filepath . "-unencrypted";
        if ($this->landscape)
            $pdf = new FPDF("L");
        else
            $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Courier', 'B', 16);
        $pdf->Cell(40, 10, 'user.wifi Service');
        $pdf->Ln(20);
        $pdf->Cell(80, 10, $this->subject);
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 12);
        // Write Body

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->message) as $line)
        {
            if ($line == "%TABLE%")
                $this->PdfSqlTable($pdf, $table);
            else
                $pdf->Write(5, $line . "\n");
        }
        $pdf->Output($un_filename);
        $this->encryptPdf($un_filename);
    }

    private function encryptPdf($filename)
    {
        $this->setRandomPdfPassword();
        exec("/usr/bin/qpdf --encrypt " . $this->password . " - 256 -- " . $filename .
            " " . $this->filepath);
        unlink($filename);
    }

    private function PdfSqlTable($pdf, $table)
    {

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

        foreach ($table as $row[$totalrows])
        {
            $column = 0;

            while (isset($row[$totalrows][$column]))
            {
                $collength = strlen($row[$totalrows][$column]);
                if ($w[$column] < $collength)
                    $w[$column] = 10+ ($collength * 3);
                $column++;
            }
            $totalrows++;
        }
        for ($rownum = 0; $rownum <= $totalrows; $rownum++)
        {
            $column = 0;

            while (isset($row[$rownum][$column]))
            {
                $pdf->Cell($w[$column], 6, $row[$rownum][$column], 1, 0, 'C');
                $column++;
            }
            $pdf->Ln();
        }
    }

    private function setRandomPdfPassword()
    {
        $config = config::getInstance();
        $length = $config->values['pdf-password']['length'];
        $pattern = $config->values['pdf-password']['regex'];
        $pass = preg_replace($pattern, "", base64_encode($this->strongRandomBytes($length *
            4)));
        $this->password = substr($pass, 0, $length);
    }

    private function strongRandomBytes($length)
    {
        $strong = false; // Flag for whether a strong algorithm was used
        $bytes = openssl_random_pseudo_bytes($length, $strong);

        if (!$strong)
        {
            // System did not use a cryptographically strong algorithm
            throw new Exception('Strong algorithm not available for PRNG.');
        }

        return $bytes;
    }


}

?>
