<?php

namespace App\Http\Controllers;

use App\Models\RawatJlDr;
use App\Models\JnsPerawatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardTindakanController extends Controller
{
    public function index(Request $request)
    {
        $today = date('Y-m-d');
        
        // Set default date range
        $start_date = $request->has('start_date') && $request->start_date 
                        ? $request->start_date 
                        : $today;
        $end_date = $request->has('end_date') && $request->end_date 
                        ? $request->end_date 
                        : $today;
        
        // Base query untuk mengambil data tindakan
        $query = RawatJlDr::query()
            ->with(['regPeriksa.pasien', 'jnsPerawatan', 'dokter'])
            ->whereBetween('tgl_perawatan', [$start_date, $end_date])
            ->orderBy('tgl_perawatan', 'desc')
            ->orderBy('jam_rawat', 'asc');

        // Filter by treatment type (jenis perawatan)
        if ($request->has('kd_jenis_prw') && $request->kd_jenis_prw) {
            $query->where('kd_jenis_prw', $request->kd_jenis_prw);
        }

        // Filter by doctor
        if ($request->has('kd_dokter') && $request->kd_dokter) {
            $query->where('kd_dokter', $request->kd_dokter);
        }

        // Filter by payment status
        if ($request->has('stts_bayar') && $request->stts_bayar) {
            $query->where('stts_bayar', $request->stts_bayar);
        }

        // Search by patient name or medical record number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('regPeriksa.pasien', function ($q) use ($search) {
                $q->where('nm_pasien', 'like', "%{$search}%")
                  ->orWhere('no_rkm_medis', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $tindakans = $query->paginate(15)->appends($request->query());

        // Get all treatment types for filter dropdown
        $jnsPerawatans = JnsPerawatan::orderBy('nm_perawatan')->get();

        // Get all doctors for filter dropdown
        $dokters = DB::table('dokter')->orderBy('nm_dokter')->get();

        // Get statistics based on selected date range
        $statsQuery = RawatJlDr::whereBetween('tgl_perawatan', [$start_date, $end_date]);
        
        $stats = [
            'total_tindakan' => (clone $statsQuery)->count(),
            'total_pasien_unik' => (clone $statsQuery)->distinct('no_rawat')->count(),
            'total_biaya' => (clone $statsQuery)->sum('biaya_rawat'),
            'belum_bayar' => (clone $statsQuery)->where('stts_bayar', 'Belum')->count(),
        ];

        return view('dashboard.tindakan.index', compact(
            'tindakans',
            'jnsPerawatans',
            'dokters',
            'stats',
            'start_date',
            'end_date',
            'today'
        ));
    }

    public function show($no_rawat, $kd_jenis_prw, $kd_dokter, $tgl_perawatan, $jam_rawat)
    {
        $tindakan = RawatJlDr::where('no_rawat', $no_rawat)
            ->where('kd_jenis_prw', $kd_jenis_prw)
            ->where('kd_dokter', $kd_dokter)
            ->where('tgl_perawatan', $tgl_perawatan)
            ->where('jam_rawat', $jam_rawat)
            ->with(['regPeriksa.pasien', 'jnsPerawatan', 'dokter'])
            ->firstOrFail();

        return view('dashboard.tindakan.show', compact('tindakan'));
    }

    public function export(Request $request)
    {
        $today = date('Y-m-d');
        
        // Set default date range
        $start_date = $request->has('start_date') && $request->start_date 
                        ? $request->start_date 
                        : $today;
        $end_date = $request->has('end_date') && $request->end_date 
                        ? $request->end_date 
                        : $today;
        
        // Base query untuk mengambil data tindakan
        $query = RawatJlDr::query()
            ->with(['regPeriksa.pasien', 'jnsPerawatan', 'dokter'])
            ->whereBetween('tgl_perawatan', [$start_date, $end_date])
            ->orderBy('tgl_perawatan', 'desc')
            ->orderBy('jam_rawat', 'asc');

        // Apply filters sama seperti index
        if ($request->has('kd_jenis_prw') && $request->kd_jenis_prw) {
            $query->where('kd_jenis_prw', $request->kd_jenis_prw);
        }

        if ($request->has('kd_dokter') && $request->kd_dokter) {
            $query->where('kd_dokter', $request->kd_dokter);
        }

        if ($request->has('stts_bayar') && $request->stts_bayar) {
            $query->where('stts_bayar', $request->stts_bayar);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('regPeriksa.pasien', function ($q) use ($search) {
                $q->where('nm_pasien', 'like', "%{$search}%")
                  ->orWhere('no_rkm_medis', 'like', "%{$search}%");
            });
        }

        // Get all data
        $tindakans = $query->get();

        $filename = 'Laporan-Tindakan-' . date('d-m-Y', strtotime($start_date)) . '-' . date('d-m-Y', strtotime($end_date)) . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        
        // Create Excel XLSX file
        $this->createExcelFile($tempFile, $tindakans, $start_date, $end_date);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function createExcelFile($filePath, $tindakans, $start_date, $end_date)
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            
            // Create [Content_Types].xml
            $zip->addFromString('[Content_Types].xml', $this->getContentTypes());
            
            // Create _rels/.rels
            $zip->addFromString('_rels/.rels', $this->getRelsXml());
            
            // Create xl/workbook.xml
            $zip->addFromString('xl/workbook.xml', $this->getWorkbookXml());
            
            // Create xl/_rels/workbook.xml.rels
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRelsXml());
            
            // Create styles.xml
            $zip->addFromString('xl/styles.xml', $this->getStylesXml());
            
            // Create sheet data
            $sheetContent = $this->getSheetContent($tindakans, $start_date, $end_date);
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheetContent);
            
            $zip->close();
        }
    }

    private function getContentTypes()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
    }

    private function getRelsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    }

    private function getWorkbookXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Tindakan" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
    }

    private function getWorkbookRelsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
    }

    private function getStylesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="2">
    <numFmt numFmtId="164" formatCode="#,##0"/>
    <numFmt numFmtId="165" formatCode="mm/dd/yyyy"/>
  </numFmts>
  <fonts count="2">
    <font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>
    <font><sz val="12"/><bold val="1"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF00897B"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="5">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"/>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>
    <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0"/>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="right"/>
    </xf>
  </cellXfs>
</styleSheet>';
    }

    private function getSheetContent($tindakans, $start_date, $end_date)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheetData>';

        $rowNum = 1;
        
        // Title row
        $xml .= '<row r="' . $rowNum . '"><c r="A' . $rowNum . '" s="1" t="str"><v>LAPORAN DATA TINDAKAN</v></c></row>';
        $rowNum++;
        
        // Period row
        $periodText = 'Periode: ' . \Carbon\Carbon::parse($start_date)->format('d-m-Y') . ' s/d ' . \Carbon\Carbon::parse($end_date)->format('d-m-Y');
        $xml .= '<row r="' . $rowNum . '"><c r="A' . $rowNum . '" s="1" t="str"><v>' . htmlspecialchars($periodText) . '</v></c></row>';
        $rowNum++;
        
        // Empty row
        $xml .= '<row r="' . $rowNum . '"/>';
        $rowNum++;
        
        // Header row
        $headers = ['No', 'Tanggal', 'Waktu', 'No. Rawat', 'No. Rekam Medis', 'Nama Pasien', 'Jenis Tindakan', 'Dokter', 'Total Biaya', 'No. HP'];
        $xml .= '<row r="' . $rowNum . '" ht="20">';
        $col = 'A';
        foreach ($headers as $header) {
            $xml .= '<c r="' . $col . $rowNum . '" s="1" t="str"><v>' . $header . '</v></c>';
            $col++;
        }
        $xml .= '</row>';
        $rowNum++;
        
        // Data rows
        $no = 1;
        foreach ($tindakans as $tindakan) {
            $xml .= '<row r="' . $rowNum . '">';
            
            // No
            $xml .= '<c r="A' . $rowNum . '" s="2" t="n"><v>' . $no . '</v></c>';
            
            // Tanggal
            $xml .= '<c r="B' . $rowNum . '" s="2" t="str"><v>' . \Carbon\Carbon::parse($tindakan->tgl_perawatan)->format('d-m-Y') . '</v></c>';
            
            // Waktu
            $xml .= '<c r="C' . $rowNum . '" s="2" t="str"><v>' . \Carbon\Carbon::parse($tindakan->jam_rawat)->format('H:i') . '</v></c>';
            
            // No. Rawat
            $xml .= '<c r="D' . $rowNum . '" s="2" t="str"><v>' . htmlspecialchars($tindakan->no_rawat) . '</v></c>';
            
            // No. Rekam Medis
            $xml .= '<c r="E' . $rowNum . '" s="2" t="str"><v>' . htmlspecialchars($tindakan->regPeriksa->pasien->no_rkm_medis ?? '-') . '</v></c>';
            
            // Nama Pasien
            $xml .= '<c r="F' . $rowNum . '" s="2" t="str"><v>' . htmlspecialchars($tindakan->regPeriksa->pasien->nm_pasien ?? '-') . '</v></c>';
            
            // Jenis Tindakan
            $xml .= '<c r="G' . $rowNum . '" s="2" t="str"><v>' . htmlspecialchars($tindakan->jnsPerawatan->nm_perawatan ?? $tindakan->kd_jenis_prw) . '</v></c>';
            
            // Dokter
            $xml .= '<c r="H' . $rowNum . '" s="2" t="str"><v>' . htmlspecialchars($tindakan->dokter->nm_dokter ?? '-') . '</v></c>';
            
            // Total Biaya
            $xml .= '<c r="I' . $rowNum . '" s="3" t="n"><v>' . (int)($tindakan->total_biaya ?? 0) . '</v></c>';
            
            // No. HP
            $xml .= '<c r="J' . $rowNum . '" s="2" t="str"><v>' . htmlspecialchars($tindakan->regPeriksa->pasien->no_tlp ?? '-') . '</v></c>';
            
            $xml .= '</row>';
            $rowNum++;
            $no++;
        }
        
        $xml .= '</sheetData>
  <mergeCells count="2">
    <mergeCell ref="A1:J1"/>
    <mergeCell ref="A2:J2"/>
  </mergeCells>
  <colDimensions>
    <colDimension min="1" max="1" width="6"/>
    <colDimension min="2" max="2" width="14"/>
    <colDimension min="3" max="3" width="10"/>
    <colDimension min="4" max="4" width="14"/>
    <colDimension min="5" max="5" width="15"/>
    <colDimension min="6" max="6" width="25"/>
    <colDimension min="7" max="7" width="20"/>
    <colDimension min="8" max="8" width="18"/>
    <colDimension min="9" max="9" width="14"/>
    <colDimension min="10" max="10" width="18"/>
  </colDimensions>
</worksheet>';

        return $xml;
    }
}
