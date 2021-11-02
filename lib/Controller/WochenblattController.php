<?php

namespace OCA\Stundenzettel\Controller;

use OCA\Stundenzettel\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DownloadResponse;
use OCP\IRequest;
use \PDO;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WochenblattController extends Controller
{
    private $dl_filename;

    public function __construct(IRequest $request)
    {
        parent::__construct(Application::APP_ID, $request);
        $this->includePhpSpreadSheet();
    }

    /**
     * @return PDO
     */
    private function getDbh(): PDO
    {
        include('stundenzettel/lib/config.php');

        $conn = $db_config['system'] . ':host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'] . ';port=' . $db_config['port'];
        $dbh = new \PDO($conn, $db_config['user'], $db_config['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

    /**
     * @param Worksheet $sheet
     * @param String $zeilennummer
     * @param $content
     */
    private function fillZeile($sheet, $zeilennummer, $content)
    {
        $sheet->setCellValue('A' . $zeilennummer, $content['feiertag'] ? 'F' : '');
        $sheet->setCellValue('B' . $zeilennummer, $content['arbeitszeitverlagerung'] ? 'A' : '');

        $d = strtotime($content['datum']);
        $wochentag = strftime("%a", $d);
        $datum = strftime("%d.%m.%Y", $d);
        $sheet->setCellValue('C' . $zeilennummer, $wochentag); // Spalte C, Wochentag wird aus D berechnet
        $sheet->setCellValue('D' . $zeilennummer, $datum);

        $sheet->setCellValue('E' . $zeilennummer, $content['von']);
        $sheet->setCellValue('F' . $zeilennummer, $content['bis']);
        $sheet->setCellValue('G' . $zeilennummer, $content['auftragsnr']);
        $sheet->setCellValue('H' . $zeilennummer, $content['bauvorhaben']);

        $s = number_format($content['stunden'], 2, ',', '.');
        $col = $content['ueberstunden'] ? 'J' : 'I';
        $sheet->setCellValue($col . $zeilennummer, $s);

        $sheet->setCellValue('K' . $zeilennummer, $content['lohnart']);

        $es = $content['erschwer_stunden'];
        if (0 == $es) $es = ''; 
        $sheet->setCellValue('L' . $zeilennummer, $es);
        $sheet->setCellValue('M' . $zeilennummer, $content['erschwer_nr']);
        $sheet->setCellValue('N' . $zeilennummer, $content['erschwer_taetigkeit']);

        $b = $content['rufbereitschaft'] ? 'B' : '';
        $sheet->setCellValue('O' . $zeilennummer, $b);
        $sheet->setCellValue('P' . $zeilennummer, $content['verpflegungsmehraufwand']);
    }

    /**
     * @param String $mitarbeiter
     * @param String $wochenbeginn
     * @param Worksheet $sheet
     * @param PDO $dbh
     */
    private function fillTable($mitarbeiter, $wochenbeginn, $sheet, $dbh)
    {
        $sql = "select to_char(e.datum, 'YYYY-MM-DD') as datum, 
            to_char(e.von, 'HH24:MI') as von, 
            to_char(e.bis, 'HH24:MI') as bis,
            e.feiertag, e.arbeitszeitverlagerung, e.auftragsnr, e.bauvorhaben, 
            e.stunden, e.ueberstunden, e. lohnart,
            e.erschwer_stunden , e.erschwer_nr, e.erschwer_taetigkeit, 
            e.rufbereitschaft, e.verpflegungsmehraufwand
            FROM oc_zeiterf_entry e 
                inner join oc_zeiterf_wochenblatt w on (e.oc_zeiterf_wochenblatt_id=w.id)
                inner join oc_zeiterf_user u on (w.oc_zeiterf_user_id=u.id)
            WHERE u.uid=:uid and w.wochenbeginn=:wochenbeginn
            ORDER BY e.datum, e.von, e.bis;";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':uid', $mitarbeiter);
        $stmt->bindParam(':wochenbeginn', $wochenbeginn);
        $stmt->execute();
        $zeilen_nr = 12;
        while ($content = $stmt->fetch()) {
            $this->fillZeile($sheet, $zeilen_nr, $content);
            $zeilen_nr++;
        }
        $stmt->closeCursor();
    }

    /**
     * @param String $mitarbeiter
     * @param Worksheet $sheet
     * @param PDO $dbh
     * @param String $KW
     *
     * @return String
     */
    private function fillStammdaten($mitarbeiter, $sheet, $dbh, $KW): String
    {
        $personalnr = '';
        $displayname = '';
        $sql = 'select o.displayname, u.personalnr
            FROM oc_users o inner join oc_zeiterf_user u on (o.uid=u.uid)
            WHERE o.uid=:uid;';
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':uid', $mitarbeiter);
        $stmt->execute();
        if ($content = $stmt->fetch()) {
            $personalnr = $content['personalnr'];
            $displayname = $content['displayname'];
        }
        $stmt->closeCursor();

        $sheet->setCellValue('G2', $personalnr);
        $sheet->setCellValue('I2', $displayname);

        $this->dl_filename = '' . $KW . '_' . $personalnr . '_' . $displayname . '.xlsx';

        return $displayname;
    }

    /**
     * @param String $wochenbeginn
     * @param String $mitarbeiter
     *
     * @return String
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createWochenblatt($wochenbeginn, $mitarbeiter): String
    {
        setlocale(LC_TIME, 'de_DE.utf8');
        try {
            $w_beginn = strtotime($wochenbeginn);
        } catch (\Exception $e) {
            return null;  // IDEA Message rausbringen
        }

        $vorlage = __DIR__ . '/vorlage.xlsx';
        $spreadsheet = IOFactory::load($vorlage);


        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $jahr = strftime('%Y', $w_beginn);
        $sheet->setCellValue('C2', $jahr);

        $sheet->setCellValue('D2', strftime('%B', $w_beginn));  // deutscher, vollst�ndiger Monatsname
        $kalenderwoche = strftime('%V', $w_beginn);
        $sheet->setCellValue('E2', $kalenderwoche);

        $dbh = $this->getDbh();
        $displayname = $this->fillStammdaten($mitarbeiter, $sheet, $dbh, $kalenderwoche);

        $s = 'Stundenzettel ' . $displayname . ' ' . $kalenderwoche . '/' . $jahr;
        $spreadsheet->getProperties()
            ->setTitle($s)
            ->setSubject($s);
        // IDEA ->setLastModifiedBy($displayname) UID des Exporteurs eintrag

        $this->fillTable($mitarbeiter, $wochenbeginn, $sheet, $dbh);

        $xlsx_file = \OC::$server->getTempManager()->getTemporaryFile('xlsx');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($xlsx_file);

        return $xlsx_file;
    }

    public function getDownloadFilename() {
        return $this->dl_filename;
    }

    /**
     * @param String $wochenbeginn
     * @param String $mitarbeiter
     *
     * @return FileDownloadResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function download($wochenbeginn, $mitarbeiter)
    {
        $xlsx_file = $this->createWochenblatt($wochenbeginn, $mitarbeiter);
        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return new FileDownloadResponse($this->dl_filename, $contentType, $xlsx_file);
    }


    /**
     * Hilfsroutine zum laden aller notwendigen PHP-Files f�r den Export.
     */
    private function includePhpSpreadSheet()
    {

        include_once __DIR__ . '/phpspreadsheet/Spreadsheet.php';
        include_once __DIR__ . '/phpspreadsheet/Calculation/Calculation.php';
        include_once __DIR__ . '/phpspreadsheet/Calculation/Category.php';
        include_once __DIR__ . '/phpspreadsheet/Calculation/Engine/CyclicReferenceStack.php';
        include_once __DIR__ . '/phpspreadsheet/Calculation/Engine/Logger.php';
        include_once __DIR__ . '/phpspreadsheet/ReferenceHelper.php';
        include_once __DIR__ . '/phpspreadsheet/IComparable.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/Worksheet.php';
        include_once __DIR__ . '/phpspreadsheet/Shared/StringHelper.php';
        include_once __DIR__ . '/phpspreadsheet/Shared/File.php';
        include_once __DIR__ . '/phpspreadsheet/Collection/CellsFactory.php';
        include_once __DIR__ . '/phpspreadsheet/Collection/Cells.php';
        include_once __DIR__ . '/phpspreadsheet/Collection/Cells.php';
        include_once __DIR__ . '/phpspreadsheet/Settings.php';
        include_once __DIR__ . '/phpspreadsheet/CacheInterface.php';
        include_once __DIR__ . '/phpspreadsheet/Collection/Memory.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/PageSetup.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/PageMargins.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/HeaderFooter.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/SheetView.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/Protection.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/Dimension.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/RowDimension.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/ColumnDimension.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/AutoFilter.php';
        include_once __DIR__ . '/phpspreadsheet/Document/Properties.php';
        include_once __DIR__ . '/phpspreadsheet/Document/Security.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Supervisor.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Style.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Font.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Color.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Fill.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Borders.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Border.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Alignment.php';
        include_once __DIR__ . '/phpspreadsheet/Style/NumberFormat.php';
        include_once __DIR__ . '/phpspreadsheet/Style/Protection.php';
        include_once __DIR__ . '/phpspreadsheet/Cell/Coordinate.php';
        include_once __DIR__ . '/phpspreadsheet/Cell/Cell.php';
        include_once __DIR__ . '/phpspreadsheet/Cell/DataType.php';
        include_once __DIR__ . '/phpspreadsheet/Cell/IValueBinder.php';
        include_once __DIR__ . '/phpspreadsheet/Cell/DefaultValueBinder.php';
        include_once __DIR__ . '/phpspreadsheet/IOFactory.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/IReader.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/BaseReader.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/IReadFilter.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/DefaultReadFilter.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Security/XmlScanner.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/BaseParserClass.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/Theme.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/Properties.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/Styles.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/SheetViews.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/SheetViewOptions.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/ColumnAndRowAttributes.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/PageSetup.php';
        include_once __DIR__ . '/phpspreadsheet/Reader/Xlsx/Hyperlinks.php';
        include_once __DIR__ . '/phpspreadsheet/RichText/ITextElement.php';
        include_once __DIR__ . '/phpspreadsheet/RichText/TextElement.php';
        include_once __DIR__ . '/phpspreadsheet/RichText/RichText.php';
        include_once __DIR__ . '/phpspreadsheet/RichText/Run.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/IWriter.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/BaseWriter.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/WriterPart.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/StringTable.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/ContentTypes.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/DocProps.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Rels.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Theme.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Workbook.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Style.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Worksheet.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Drawing.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Comments.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/Chart.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/RelsVBA.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/RelsRibbon.php';
        include_once __DIR__ . '/phpspreadsheet/HashTable.php';
        include_once __DIR__ . '/phpspreadsheet/Worksheet/Iterator.php';
        include_once __DIR__ . '/phpspreadsheet/Exception.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Exception.php';
        include_once __DIR__ . '/phpspreadsheet/Calculation/Functions.php';
        include_once __DIR__ . '/phpspreadsheet/Shared/XMLWriter.php';
        include_once __DIR__ . '/phpspreadsheet/Shared/Date.php';
        include_once __DIR__ . '/phpspreadsheet/Writer/Xlsx/DefinedNames.php';

        include_once __DIR__ . '/myclabs/Enum.php';

        include_once __DIR__ . '/zipstream/Stream.php';
        include_once __DIR__ . '/zipstream/Bigint.php';
        include_once __DIR__ . '/zipstream/Option/Version.php';
        include_once __DIR__ . '/zipstream/Option/Archive.php';
        include_once __DIR__ . '/zipstream/Option/File.php';
        include_once __DIR__ . '/zipstream/Option/Method.php';
        include_once __DIR__ . '/zipstream/File.php';
        include_once __DIR__ . '/zipstream/Exception.php';
        include_once __DIR__ . '/zipstream/Exception/FileNotFoundException.php';
        include_once __DIR__ . '/zipstream/Exception/FileNotReadableException.php';
        include_once __DIR__ . '/zipstream/Exception/IncompatibleOptionsException.php';
        include_once __DIR__ . '/zipstream/Exception/EncodingException.php';
        include_once __DIR__ . '/zipstream/Exception/StreamNotReadableException.php';
        include_once __DIR__ . '/zipstream/Exception/OverflowException.php';
        include_once __DIR__ . '/zipstream/DeflateStream.php';
        include_once __DIR__ . '/zipstream/ZipStream.php';
    }
}

class FileDownloadResponse extends DownloadResponse
{

    private $src_file_name;

    /**
     * Creates a response that prompts the user to download the text
     * @param string $filename the name that the downloaded file should have
     * @param string $contentType the mimetype that the downloaded file should have
     * @param string $src_file_name file to download
     * @since 8.0.0
     */
    public function __construct($filename, $contentType, $src_file_name)
    {
        $this->src_file_name = $src_file_name;
        parent::__construct($filename, $contentType);
    }

    /**
     * @return string
     */
    public function render()
    {
        $data = file_get_contents($this->src_file_name);
        unlink($this->src_file_name);

        return $data;
    }
}
