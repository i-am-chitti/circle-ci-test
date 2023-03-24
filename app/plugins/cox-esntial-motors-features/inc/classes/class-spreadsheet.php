<?php
/**
 * SpreadSheet Export functionality.
 *
 * @package cox-esntial-motors-features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use PhpOffice\PhpSpreadsheet\Spreadsheet as PHPSpreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;

/**
 * SpreadSheet class.
 */
class Spreadsheet {

	/**
	 * Status.
	 *
	 * @var bool
	 */
	public static $changed = true;

	/**
	 * Export the excel file.
	 *
	 * @param int $form_id  :   form id of the form whose entry needs to be exported.
	 * @param int $entry_id :   entry id of the entry which need to be exported.
	 */
	public static function export( $form_id, $entry_id ) {

		$spreadsheet = self::get_excel( $form_id, $entry_id );

		// unique file name by date time.
		$filename = 'export-' . gmdate( 'Y-m-d-H-i-s' ) . '.xlsx';

		$writer = new Xlsx( $spreadsheet );
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );
		$writer->save( 'php://output' );
	}

	/**
	 * Generate Excel File for particular Entry.
	 *
	 * @param int $form_id  :   form id of the form whose entry needs to be exported.
	 * @param int $entry_id :   entry id of the entry which need to be exported.
	 *
	 * @return PHPSpreadsheet
	 */
	public static function get_excel( $form_id, $entry_id ) {
		$row          = 1;
		$curr_page    = 0;
		$prev_page    = 0;
		$curr_section = 0;
		$prev_section = 0;

		// get the particular entry.
		$entry = \GFAPI::get_entry( $entry_id );
		$form  = \GFAPI::get_form( $form_id );
		$count = count( $form['fields'] );

		if ( empty( $entry ) || empty( $form ) ) {
			return;
		}

		$spreadsheet = new PHPSpreadsheet();

		for ( $i = 0; $i < $count; $i++ ) {

			$curr_page    = $form['fields'][ $i ]->pageNumber;
			$curr_section = $form['fields'][ $i ]->customSection;

			// check if page or section is changed.

			if ( self::page_status( $curr_page, $prev_page ) === self::$changed ) {
				// border bottom for previous page.
				if ( $prev_page > 0 ) {
					$sheet = $spreadsheet->getActiveSheet();
					$sheet->getStyle( 'A' . ( $row - 1 ) . ':D' . ( $row - 1 ) )->applyFromArray( array( 'borders' => array( 'bottom' => array( 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ) ) ) );
				}

				$row = 1;
				self::generate_page( $spreadsheet, $prev_page, $row, $form );
				$row++;
			}

			if ( self::section_status( $curr_section, $prev_section ) === self::$changed ) {
				self::generate_section( $spreadsheet, $curr_section, $row );
				$row++;
			}

			// write field data.

			self::write_field_data( $spreadsheet, $row, $form, $entry, $i );
			$row++;

			// set previous page and section.

			if ( ! empty( $curr_page ) ) {
				$prev_page = $curr_page;
			}

			if ( ! empty( $curr_section ) ) {
				$prev_section = $curr_section;
			}

			if ( ( $count - 1 ) === $i ) {
				// border bottom for last page.
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->getStyle( 'A' . ( $row - 1 ) . ':D' . ( $row - 1 ) )->applyFromArray( array( 'borders' => array( 'bottom' => array( 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ) ) ) );
			}
		}

		return $spreadsheet;
	}

	/**
	 * Generate the Page in the Spreadsheet.
	 *
	 * @param PHPSpreadsheet $spreadsheet :   Spreadsheet object reference.
	 * @param int            $prev_page   :   previous page number.
	 * @param int            $row         :   row number reference.
	 * @param array          $form        :   form array reference.
	 *
	 * @return void.
	 */
	public static function generate_page( &$spreadsheet, $prev_page, &$row, &$form ) {

		$title = $form['pagination']['pages'][ $prev_page ];
		if ( empty( $title ) ) {
			$title = 'Page-' . $prev_page;
		}

		$spreadsheet->createSheet();
		$spreadsheet->setActiveSheetIndex( $prev_page );
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle( $title );

		// set header.
		self::generate_header( $spreadsheet, $row, $title );
	}

	/**
	 * Generate the Header in the Spreadsheet.
	 *
	 * @param PHPSpreadsheet $spreadsheet :   Spreadsheet object reference.
	 * @param int            $row         :   row number reference.
	 * @param string         $title       :   title of the header.
	 *
	 * @return void.
	 */
	public static function generate_header( &$spreadsheet, &$row, &$title ) {

		$sheet = $spreadsheet->getActiveSheet();
		// merge 5 cells.
		$sheet->mergeCells( 'A' . $row . ':D' . $row );

		// sDt header title.
		$sheet->setCellValue( 'A' . $row, $title );

		$sheet->getStyle( 'A' . $row )->getFont()->setSize( 18 );
		$sheet->getStyle( 'A' . $row )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER );
		$sheet->getStyle( 'A' . $row )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
		$sheet->getStyle( 'A' . $row . ':D' . $row )->getBorders()->getAllBorders()->setBorderStyle( \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN );

		// set row height.
		$sheet->getRowDimension( $row )->setRowHeight( 30 );

		// set column width.
		$sheet->getColumnDimension( 'A' )->setWidth( 45 );
		$sheet->getColumnDimension( 'B' )->setWidth( 45 );
		$sheet->getColumnDimension( 'C' )->setWidth( 45 );
		$sheet->getColumnDimension( 'D' )->setWidth( 45 );

		$row++;
		// set column header.
		$sheet->getRowDimension( $row )->setRowHeight( 25 );
		$sheet->getStyle( 'A' . $row . ':D' . $row )->getFont()->setSize( 14 );

		$sheet->setCellValue( 'A' . $row, 'Fields / Sections' );
		$sheet->setCellValue( 'B' . $row, 'Form Response' );
		$sheet->setCellValue( 'C' . $row, 'StaticTextSection' );
		$sheet->setCellValue( 'D' . $row, 'TranslationEntry' );

		// set all borders.
		$sheet->getStyle( 'A' . $row . ':D' . $row )->getBorders()->getAllBorders()->setBorderStyle( \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN );
	}

	/**
	 * Generate the Section in the Spreadsheet.
	 *
	 * @param PHPSpreadsheet $spreadsheet  :   Spreadsheet object reference.
	 * @param string         $curr_section :   current section.
	 * @param int            $row          :   row number reference.
	 *
	 * @return void.
	 */
	public static function generate_section( $spreadsheet, $curr_section, $row ) {
		$sheet = $spreadsheet->getActiveSheet();

		// merge 5 cells.
		$sheet->mergeCells( 'A' . $row . ':D' . $row );

		// set font to 14 and bold.
		$sheet->getStyle( 'A' . $row )->getFont()->setSize( 14 );

		// set all borders.
		$sheet->getStyle( 'A' . $row . ':D' . $row )->getBorders()->getAllBorders()->setBorderStyle( \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN );

		// set Section title.
		$sheet->setCellValue( 'A' . $row, $curr_section );
	}

	/**
	 * Generate the Field in the Spreadsheet.
	 *
	 * @param PHPSpreadsheet $spreadsheet :   Spreadsheet object reference.
	 * @param int            $row         :   row number reference.
	 * @param array          $form        :   current form array.
	 * @param array          $entry       :   current entry array.
	 * @param int            $i           :   field index.
	 *
	 * @return void.
	 */
	public static function write_field_data( &$spreadsheet, &$row, &$form, &$entry, $i ) {
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue( 'A' . $row, $form['fields'][ $i ]->label );
		$sheet->setCellValue( 'C' . $row, $form['fields'][ $i ]->customStaticTextSection );
		$sheet->setCellValue( 'D' . $row, $form['fields'][ $i ]->customTranslationEntry );
		// border right to D column.
		$sheet->getStyle( 'D' . $row )->getBorders()->getRight()->setBorderStyle( \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN );

		// check type for fileupload.
		if ( 'fileupload' === $form['fields'][ $i ]->type ) {

			$file_url = str_replace( '\\', '', $entry[ $form['fields'][ $i ]->id ] );
			$file_url = str_replace( '[', '', $file_url );
			$file_url = str_replace( ']', '', $file_url );
			$file_url = str_replace( '"', '', $file_url );

			// enable wordwrap for long url in sheet.
			$sheet->getStyle( 'B' . $row )->getAlignment()->setWrapText( true );

			$sheet->setCellValue( 'B' . $row, $file_url );

			if ( ! empty( $entry['resume_token'] ) && empty( $file_url ) ) {
				$submission = \GFFormsModel::get_draft_submission_values( $entry['resume_token'] );
				$submission = json_decode( $submission['submission'], true );

				$folder = \RGFormsModel::get_upload_url( $form['id'] ) . '/tmp/';
				$files  = $submission['files'][ 'input_' . $form['fields'][ $i ]->id ];

				$file_url = $folder . $files[0]['temp_filename'];
				if ( is_array( $files ) ) {
					$count = count( $files );
					for ( $j = 1; $j < $count; $j++ ) {
						$file_url .= ',' . PHP_EOL . $folder . $files[ $j ]['temp_filename'];
					}
				}

				! ( $file_url === $folder ) ? $sheet->setCellValue( 'B' . $row, $file_url ) : '';
			}
		} else {
			$sheet->setCellValue( 'B' . $row, $entry[ $form['fields'][ $i ]->id ] );
		}
	}

	/**
	 * Check page change status.
	 *
	 * @param string $current : current page.
	 * @param string $prev    : previous page.
	 */
	public static function page_status( &$current, &$prev ) {

		if ( empty( $current ) ) {
			return false;
		}

		return $current !== $prev;
	}

	/**
	 * Check section change status.
	 *
	 * @param string $current : current section.
	 * @param string $prev    : previous section.
	 */
	public static function section_status( &$current, &$prev ) {

		if ( empty( $current ) ) {
			return false;
		}

		return $current !== $prev;
	}
}
