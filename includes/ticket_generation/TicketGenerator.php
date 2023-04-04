<?php

namespace KjG_Ticketing\ticket_generation;

use KjG_Ticketing\database\DatabaseConnection;
use KjG_Ticketing\database\dto\Entrance;
use KjG_Ticketing\database\dto\Event;
use KjG_Ticketing\database\dto\Process;
use KjG_Ticketing\database\dto\Seat;
use KjG_Ticketing\database\dto\SeatingPlanArea;
use KjG_Ticketing\database\dto\SeatState;
use KjG_Ticketing\database\dto\Show;
use KjG_Ticketing\database\dto\TicketConfig;
use KjG_Ticketing\database\dto\TicketImageConfig;
use KjG_Ticketing\database\dto\TicketSeatingPlanConfig;
use KjG_Ticketing\database\dto\TicketTextConfig;
use KjG_Ticketing\pdf\misc\StringReader;
use KjG_Ticketing\pdf\document\ContentStream;
use KjG_Ticketing\pdf\document\Font;
use KjG_Ticketing\pdf\document\GraphicsStateParameterDictionary;
use KjG_Ticketing\pdf\document\PdfDate;
use KjG_Ticketing\pdf\document\XObjectImage;
use KjG_Ticketing\pdf\graphics\AnalyzedContentStream;
use KjG_Ticketing\pdf\graphics\ColorRGB;
use KjG_Ticketing\pdf\graphics\GenerateContentStream;
use KjG_Ticketing\pdf\graphics\operator\BeginTextObjectOperator;
use KjG_Ticketing\pdf\graphics\operator\CharacterSpaceOperator;
use KjG_Ticketing\pdf\graphics\operator\ClippingPathNonzeroOperator;
use KjG_Ticketing\pdf\graphics\operator\ColorRGBFillingOperator;
use KjG_Ticketing\pdf\graphics\operator\ColorRGBStrokingOperator;
use KjG_Ticketing\pdf\graphics\operator\EndTextObjectOperator;
use KjG_Ticketing\pdf\graphics\operator\ExternalGraphicsStateOperator;
use KjG_Ticketing\pdf\graphics\operator\ExternalObjectOperator;
use KjG_Ticketing\pdf\graphics\operator\FillPathNonzeroOperator;
use KjG_Ticketing\pdf\graphics\operator\InlineImageOperator;
use KjG_Ticketing\pdf\graphics\operator\LineCapOperator;
use KjG_Ticketing\pdf\graphics\operator\LineJoinOperator;
use KjG_Ticketing\pdf\graphics\operator\LineWidthOperator;
use KjG_Ticketing\pdf\graphics\operator\ModifyTransformationMatrixOperator;
use KjG_Ticketing\pdf\graphics\operator\PathBeginOperator;
use KjG_Ticketing\pdf\graphics\operator\PathBezierOperator;
use KjG_Ticketing\pdf\graphics\operator\PathLineOperator;
use KjG_Ticketing\pdf\graphics\operator\PathRectangleOperator;
use KjG_Ticketing\pdf\graphics\operator\PopGraphicsStateOperator;
use KjG_Ticketing\pdf\graphics\operator\PushGraphicsStateOperator;
use KjG_Ticketing\pdf\graphics\operator\StrokePathOperator;
use KjG_Ticketing\pdf\graphics\operator\TextFontOperator;
use KjG_Ticketing\pdf\graphics\operator\TextMatrixOperator;
use KjG_Ticketing\pdf\graphics\operator\TextOperator;
use KjG_Ticketing\pdf\graphics\operator\TextRenderModeOperator;
use KjG_Ticketing\pdf\graphics\operator\TextRiseOperator;
use KjG_Ticketing\pdf\graphics\operator\TextScaleOperator;
use KjG_Ticketing\pdf\graphics\operator\WordSpaceOperator;
use KjG_Ticketing\pdf\graphics\Point;
use KjG_Ticketing\pdf\graphics\state\GraphicsStateStack;
use KjG_Ticketing\pdf\graphics\TransformationMatrix;
use KjG_Ticketing\pdf\object\PdfArray;
use KjG_Ticketing\pdf\object\PdfDictionary;
use KjG_Ticketing\pdf\object\PdfHexString;
use KjG_Ticketing\pdf\object\PdfName;
use KjG_Ticketing\pdf\object\PdfNumber;
use KjG_Ticketing\pdf\object\PdfString;
use KjG_Ticketing\pdf\PdfDocument;
use KjG_Ticketing\pdf\PdfFile;

require_once __DIR__ . "/../../lib/phpqrcode/qrlib.php";

/**
 * Class for generating new tickets
 */
class TicketGenerator {
    /**
     * Connection to the database to get the necessary data.
     * @var DatabaseConnection
     */
    protected DatabaseConnection $databaseConnection;
    /**
     * Single process for which a ticket should be generated
     */
    protected Process $process;

    // Save tables from database
    protected Event|null $event = null;
    /**
     * @var SeatingPlanArea[]|null
     */
    protected array|null $seating_plan_areas = null;
    /**
     * @var Entrance[]|null
     */
    protected array|null $entrances = null;
    protected TicketConfig|null $ticket_config = null;
    protected string|null $ticket_template = null;
    /**
     * @var Seat[]|null
     */
    protected array|null $seats = null;
    /**
     * @var SeatState[]|null
     */
    protected array|null $seat_states = null;
    /**
     * @var Show[]|null
     */
    protected array|null $shows = null;

    /**
     * If Data Compression for PDF Object Streams shall be allowed.
     * This is especially useful because fdpi (the Framework used for testing if the generated tickets are valid PDFs) does not support Object Streams.
     */
    protected bool $allowDataCompression = true;

    /**
     * The generated ticket PDF-file as string
     */
    protected ?string $generatedTicket = null;

    /**
     * Set a database table to avoid duplicate loading
     *
     * @param Event $event
     */
    public function setEvent( Event $event ): void {
        $this->event = $event;
    }

    /**
     * Set a database table to avoid duplicate loading
     *
     * @param SeatingPlanArea[] $seating_plan_areas
     */
    public function setSeatingplanareas( array $seating_plan_areas ): void {
        $this->seating_plan_areas = $seating_plan_areas;
    }

    /**
     * Set a database table to avoid duplicate loading
     *
     * @param Entrance[] $entrances
     */
    public function setEntrances( array $entrances ): void {
        $this->entrances = $entrances;
    }

    /**
     * Set a database table to avoid duplicate loading
     *
     * @param TicketConfig $ticket_config
     */
    public function setTicketconfig( TicketConfig $ticket_config ): void {
        $this->ticket_config = $ticket_config;
    }

    /**
     * Set the ticket template to avoid duplicate loading
     *
     * @param string $ticket_template
     */
    public function setTickettemplate( string $ticket_template ): void {
        $this->ticket_template = $ticket_template;
    }

    /**
     * Set a database table to avoid duplicate loading
     *
     * @param Seat[] $seats
     */
    public function setSeats( array $seats ): void {
        $this->seats = $seats;
    }

    /**
     * Set a database table to avoid duplicate loading
     *
     * @param SeatState[] $seat_states
     */
    public function setSeatstates( array $seat_states ): void {
        $this->seat_states = $seat_states;
    }

    /**
     * Set a database table to avoid duplicate loading
     *
     * @param Show[] $shows
     */
    public function setShows( array $shows ): void {
        $this->shows = $shows;
    }

    /**
     * If Data Compression for PDF Object Streams shall be allowed.
     * This is especially useful because fdpi (the Framework used for testing if the generated tickets are valid PDFs) does not support Object Streams.
     *
     * @param bool $allowDataCompression
     */
    public function setAllowDataCompression( bool $allowDataCompression ): void {
        $this->allowDataCompression = $allowDataCompression;
    }

    /**
     * TicketGenerator constructor.
     *
     * @param DatabaseConnection $databaseConnection Connection to the database to obtain all necessary data.
     * @param Process $process Single process for which a ticket should be generated
     */
    public function __construct( DatabaseConnection $databaseConnection, Process $process ) {
        $this->databaseConnection = $databaseConnection;
        $this->process            = $process;
    }

    /**
     * Load all data that has not yet been loaded.
     * @throws \Exception
     */
    public function loadData(): void {
        if ( $this->event === null ) {
            $this->event = $this->databaseConnection->get_event();
            if ( $this->event === false ) {
                throw new \Exception( "Could not read event from database" );
            }
        }
        if ( $this->seating_plan_areas === null ) {
            $this->seating_plan_areas = $this->databaseConnection->get_seating_plan_areas();
        }
        if ( $this->entrances === null ) {
            $this->entrances = $this->databaseConnection->get_entrances();
        }
        if ( $this->ticket_config === null ) {
            $this->ticket_config = $this->databaseConnection->get_ticket_config();
        }
        if ( $this->ticket_template === null ) {
            $this->ticket_template = $this->databaseConnection->get_ticket_template();
            if ( $this->ticket_template === null ) {
                throw new \Exception( "Could not read ticket template from database" );
            }
        }
        if ( $this->seats === null ) {
            $this->seats = $this->databaseConnection->get_seats();
        }
        if ( $this->seat_states === null ) {
            $this->seat_states = $this->databaseConnection->get_seat_states();
        }
        if ( $this->shows === null ) {
            $this->shows = $this->databaseConnection->get_shows();
        }
    }

    /**
     * Generates a ticket and stores it in $this->generatedTicket.
     * @throws \Exception When the ticket could not get generated
     * @see TicketGenerator::saveTicket() to store the ticket at a well-known place
     * @see TicketGenerator::getTicketContent() to obtain the generated ticket as string
     */
    public function generateTicket(): void {
        // -----------------------------
        // | Load data and prepare PDF |
        // -----------------------------

        $this->loadData();
        if ( $this->process->payment_method === Process::PAYMENT_METHOD_VIP || $this->process->payment_method === Process::PAYMENT_METHOD_TRIPLE_A ) {
            $price_PDF_object = new PdfName( $this->process->payment_method );
        } else {
            $price_PDF_object = new PdfNumber( $this->process->ticket_price );
        }

        // Load template
        $pdfFile = new PdfFile( new StringReader( $this->ticket_template ) );
        if ( $this->allowDataCompression ) {
            $pdfFile->setMinVersion( "1.5" );
        } // Support ObjectStreams
        $pdfDocument = new PdfDocument( $pdfFile );

        // Calculate according seat states
        $seat_states = [];
        foreach ( $this->seat_states as $seat_state ) {
            if ( @$seat_state->process_id === $this->process->id ) {
                $seat_states[] = clone $seat_state;
            }
        }

        // extract and delete template
        if ( $pdfDocument->getPageList()->getPageCount() !== 1 ) {
            throw new \Exception( "Only PDF-Files with one Page can be used as Template" );
        }
        $templatePage          = $pdfDocument->getPageList()->getPage( 0 );
        $templateContentStream = $templatePage->getContents();
        $pdfDocument->getPageList()->removePage( 0 );

        // Set document info
        $lastModified = PdfDate::parseDateTime( new \DateTime( 'now' ) );
        $pdfDocument->getDocumentCatalog()->setPieceInfo( "tickets", new PdfDictionary( [
            "LastModified"  => $lastModified,
            "ProcessId"     => new PdfNumber( $this->process->id ),
            "Price"         => $price_PDF_object,
            "PaymentState"  => new PdfString( $this->process->payment_state ),
            "NumberTickets" => new PdfNumber( count( $seat_states ) )
        ] ) );
        $pdfDocument->getDocumentInfo()->setTitle( PdfHexString::parseString( "\xfe\xff" . iconv( "UTF-8", "UTF-16BE",
                "Tickets for " . $this->event->name
            ) ) );
        $pdfDocument->getDocumentInfo()->setProducer( new PdfString( "KjG-Theater Ticketing System \xa92023, Philipp Horwat" ) );
        $pdfDocument->getDocumentInfo()->setModificationDate( $lastModified );
        $templateContentStream->getResourceDictionary()->addProcSet( new PdfName( "PDF" ) );
        $templateContentStream->getResourceDictionary()->addProcSet( new PdfName( "Text" ) );
        $templateContentStream->getResourceDictionary()->addProcSet( new PdfName( "ImageB" ) );
        $templateContentStream->getResourceDictionary()->addProcSet( new PdfName( "ImageC" ) );


        // ----------------------------------------
        // | Bilder entfernen, die ersetzt werden |
        // ----------------------------------------

        $analyzedContentStream = new AnalyzedContentStream(
            new GraphicsStateStack( new TransformationMatrix(), $templatePage->getCropBox() ),
            $templateContentStream
        );
        $qrCodeConfig          = @$this->ticket_config->qr_code_config;
        if ( $qrCodeConfig !== null && isset( $qrCodeConfig->pdf_content_stream_start_operator_index ) ) {
            $analyzedContentStream->deleteOperators(
                $qrCodeConfig->pdf_content_stream_start_operator_index,
                $qrCodeConfig->pdf_content_stream_start_operator_index + $qrCodeConfig->pdf_content_stream_num_operators
            );
            if ( $qrCodeConfig->pdf_resource_deletable ) {
                $templateContentStream->getResourceDictionary()->removeXObject( $qrCodeConfig->pdf_operator_name );
            }
        }
        $seating_plan_config = @$this->ticket_config->seating_plan_config;
        if ( $seating_plan_config !== null && isset( $seating_plan_config->pdf_content_stream_start_operator_index ) ) {
            $analyzedContentStream->deleteOperators(
                $seating_plan_config->pdf_content_stream_start_operator_index,
                $seating_plan_config->pdf_content_stream_start_operator_index + $seating_plan_config->pdf_content_stream_num_operators
            );
            if ( $seating_plan_config->pdf_resource_deletable ) {
                $templateContentStream->getResourceDictionary()->removeXObject( $seating_plan_config->pdf_operator_name );
            }
        }

        // -----------------------------------------------
        // | Create resources that are used on all pages |
        // -----------------------------------------------

        // Create fonts
        if ( isset( $this->ticket_config->seating_plan_config ) ) {
            $seating_plan_font_name = $this->getFontName( $this->ticket_config->seating_plan_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->date_text_config ) ) {
            $date_text_font_name = $this->getFontName( $this->ticket_config->date_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->time_text_config ) ) {
            $time_text_font_name = $this->getFontName( $this->ticket_config->time_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->seat_block_text_config ) ) {
            $seat_block_text_font_name = $this->getFontName( $this->ticket_config->seat_block_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->seat_row_text_config ) ) {
            $seat_row_text_font_name = $this->getFontName( $this->ticket_config->seat_row_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->seat_number_text_config ) ) {
            $seat_number_text_font_name = $this->getFontName( $this->ticket_config->seat_number_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->price_text_config ) ) {
            $price_text_font_name = $this->getFontName( $this->ticket_config->price_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->payment_state_text_config ) ) {
            $payment_state_text_font_name = $this->getFontName( $this->ticket_config->payment_state_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->process_id_text_config ) ) {
            $process_id_text_font_name = $this->getFontName( $this->ticket_config->process_id_text_config, $templateContentStream );
        }
        if ( isset( $this->ticket_config->seating_plan_config ) ) {
            // Create Images
            $seatGrayOperator = $this->getImageOperator( __DIR__ . "/../../images/seat_gray.png", $templateContentStream );
            if ( $this->process->payment_state === Process::PAYMENT_STATE_PAID ) {
                $seatLightColoredOperator = $this->getImageOperator( __DIR__ . "/../../images/seat_lightgreen.png", $templateContentStream );
                $seatColoredOperator      = $this->getImageOperator( __DIR__ . "/../../images/seat_green_selected.png", $templateContentStream );
            } else {
                $seatLightColoredOperator = $this->getImageOperator( __DIR__ . "/../../images/seat_lightyellow.png", $templateContentStream );
                $seatColoredOperator      = $this->getImageOperator( __DIR__ . "/../../images/seat_yellow_selected.png", $templateContentStream );
            }

            // Add data from $seats and $seat_states
            $seat_states_count = count( $seat_states );
            for ( $i = 0; $i < $seat_states_count; ++ $i ) {
                $seat_state = $seat_states[ $i ];
                foreach ( $this->seats as $seat ) {
                    if ( $seat_state->block === $seat->block
                         && $seat_state->reihe === $seat->row
                         && $seat_state->platz === $seat->number ) {
                        $seat_state->position_x  = $seat->position_x;
                        $seat_state->position_y  = $seat->position_y;
                        $seat_state->rotation    = $seat->rotation;
                        $seat_state->entrance_id = $seat->entrance_id;
                        $seat_state->width       = $seat->width;
                        $seat_state->length      = $seat->length;
                    }
                }
            }
        }
        // QRCode position
        if ( $qrCodeConfig !== null ) {
            $qrCodeTransformationMatrix = $this->getTransformationMatrixForImageConfig( $qrCodeConfig, 1, 1 );
        }


        // -------------------------------------------------------
        // | Create start configuration and default seating plan |
        // -------------------------------------------------------

        // Startkonfiguration setzen
        $seating_plan_start_content_stream = GenerateContentStream::generateNew( $analyzedContentStream->getLastGraphicsStateStack(), $templateContentStream->getResourceDictionary(), $pdfFile );
        $graphicsState                     = $seating_plan_start_content_stream->getLastGraphicsStateStack()->getGraphicsState();
        $textState                         = $graphicsState->getTextState();
        $transformationMatrix              = $graphicsState->getCurrentTransformationMatrix();
        if ( $transformationMatrix != new TransformationMatrix() ) {
            $seating_plan_start_content_stream->addOperator( new ModifyTransformationMatrixOperator( $transformationMatrix->invers() ) );
        }
        if ( $textState->getCharacterSpacing()->getValue() !== 0 ) {
            $seating_plan_start_content_stream->addOperator( new CharacterSpaceOperator( new PdfNumber( 0 ) ) );
        }
        if ( $textState->getWordSpacing()->getValue() !== 0 ) {
            $seating_plan_start_content_stream->addOperator( new WordSpaceOperator( new PdfNumber( 0 ) ) );
        }
        if ( $textState->getHorizontalScaling()->getValue() !== 100 ) {
            $seating_plan_start_content_stream->addOperator( new TextScaleOperator( new PdfNumber( 100 ) ) );
        }
        if ( $textState->getTextRenderMode()->getValue() !== TextRenderModeOperator::fillText ) {
            $seating_plan_start_content_stream->addOperator( new TextRenderModeOperator( new PdfNumber( TextRenderModeOperator::fillText ) ) );
        }
        if ( $textState->getTextRise()->getValue() !== 0 ) {
            $seating_plan_start_content_stream->addOperator( new TextRiseOperator( new PdfNumber( 0 ) ) );
        }
        $extGraphicsState = new GraphicsStateParameterDictionary( new PdfDictionary( [ "Type" => new PdfName( "ExtGState" ) ] ), $pdfFile );
        $extGraphicsState->generateIndirectObjectIfNotExists();
        if ( $graphicsState->getLineWidth()->getValue() !== 1 ) {
            $extGraphicsState->setLineWidth( new PdfNumber( 1 ) );
        }
        if ( $graphicsState->getLineCap() !== LineCapOperator::roundCap ) {
            $extGraphicsState->setLineCapStyle( new PdfNumber( LineCapOperator::roundCap ) );
        }
        if ( $graphicsState->getLineJoin() !== LineJoinOperator::roundJoin ) {
            $extGraphicsState->setLineJoinStyle( new PdfNumber( LineJoinOperator::roundJoin ) );
        }
        if ( ! $graphicsState->getDashPatternArray()->equals( new PdfArray( [] ) ) ) {
            $extGraphicsState->setDashPattern( new PdfArray( [] ), new PdfNumber( 0 ) );
        }
        $extGraphicsStateName = $seating_plan_start_content_stream->getContentStream()->getResourceDictionary()->addExtGState( $extGraphicsState );
        $seating_plan_start_content_stream->addOperator( new ExternalGraphicsStateOperator( new PdfName( $extGraphicsStateName ), $extGraphicsState ) );
        unset( $graphicsState, $textState, $transformationMatrix, $extGraphicsState );

        if ( $seating_plan_config !== null ) {
            // Transform in a way that the image fits exactly the seating plan size.
            $seating_plan_start_content_stream->addOperator( new PushGraphicsStateOperator() );
            $seating_plan_transformation_matrix = $this->getTransformationMatrixForImageConfig( $seating_plan_config, $this->event->seating_plan_width, $this->event->seating_plan_length );
            $seating_plan_start_content_stream->addOperator( new ModifyTransformationMatrixOperator( $seating_plan_transformation_matrix ) );
            $seating_plan_scale = $seating_plan_transformation_matrix->transformPoint( new Point( 0, 0 ) )->distanceTo( $seating_plan_transformation_matrix->transformPoint( new Point( 0, 1 ) ) );

            // Line width, font and font size
            $seating_plan_start_content_stream->addOperator( new LineWidthOperator( new PdfNumber( $seating_plan_config->line_width / $seating_plan_scale ) ) );
            $font             = $seating_plan_start_content_stream->getContentStream()->getResourceDictionary()->getFont( $seating_plan_font_name );
            $currentTextState = $seating_plan_start_content_stream->getLastGraphicsStateStack()->getGraphicsState()->getTextState();
            if ( $currentTextState->getTextFont() !== $font || $currentTextState->getTextFontSize()->getValue() !== $seating_plan_config->font_size / $seating_plan_scale ) {
                $seating_plan_start_content_stream->addOperator( new TextFontOperator( new PdfName( $seating_plan_font_name ), $font, new PdfNumber( $seating_plan_config->font_size / $seating_plan_scale ) ) );
            }

            // Draw background color and clip seating plan
            $seating_plan_start_content_stream->addOperator( new ColorRGBFillingOperator( new ColorRGB( 245 / 255, 245 / 255, 220 / 255 ) ) ); // HTML beige
            $seating_plan_start_content_stream->addOperator( new PathRectangleOperator( new PdfNumber( 0 ), new PdfNumber( 0 ), new PdfNumber( $this->event->seating_plan_width ), new PdfNumber( $this->event->seating_plan_length ) ) );
            $seating_plan_start_content_stream->addOperator( new ClippingPathNonzeroOperator() );
            $seating_plan_start_content_stream->addOperator( new FillPathNonzeroOperator() );

            // Draw all seating plan areas
            foreach ( $this->seating_plan_areas as $seating_plan_area ) {
                $this->draw_seating_plan_area( $seating_plan_area, $seating_plan_start_content_stream );
            }
            // Zeichne alle Plätze
            foreach ( $this->seats as $seat ) {
                $this->draw_seat( $seatGrayOperator, $seat, $seating_plan_start_content_stream );
            }


            // Content Stream to finalize seating plan
            $seating_plan_end_content_stream = GenerateContentStream::generateNew( $seating_plan_start_content_stream->getLastGraphicsStateStack(), $seating_plan_start_content_stream->getContentStream()->getResourceDictionary(), $pdfFile );

            $seating_plan_end_content_stream->addOperator( new BeginTextObjectOperator() );
            // draw seat numbers
            if ( $seating_plan_config->seat_numbers_visible ) {
                foreach ( $this->seats as $seat ) {
                    $this->draw_text(
                        $seat->reihe . $seat->platz,
                        $seat->position_x,
                        $seat->position_y,
                        new ColorRGB( 0, 0, 0 ),
                        $seating_plan_scale,
                        $seating_plan_end_content_stream
                    );
                }
            }
            // draw text for seating plan areas
            foreach ( $this->seating_plan_areas as $seating_plan_area ) {
                $this->draw_text(
                    $seating_plan_area->text,
                    $seating_plan_area->position_x + $seating_plan_area->text_position_x,
                    $seating_plan_area->position_y + $seating_plan_area->text_position_y,
                    ColorRGB::fromHex( $seating_plan_area->text_color ),
                    $seating_plan_scale,
                    $seating_plan_end_content_stream
                );
            }
            $seating_plan_end_content_stream->addOperator( new EndTextObjectOperator() );

            // Transform back from seating plan coordinate system to PDF coordinate system
            $seating_plan_end_content_stream->addOperator( new PopGraphicsStateOperator() );
        }


        // ---------------------------------------------------------
        // | Create a page in the PDF document for each seat state |
        // ---------------------------------------------------------

        foreach ( $seat_states as $seat_state ) {
            $show = $this->get_show( $seat_state->show_id );

            // Create page and set metadata
            $page = $templatePage->clonePage();
            $page->setContentStream( $templateContentStream );
            $page->setPieceInfo( "Ticket", new PdfDictionary( [
                "LastModified" => $lastModified,
                "Date"         => new PdfString( $show->date ),
                "Time"         => new PdfString( $show->time ),
                "SeatBlock"    => new PdfString( $seat_state->seat_block ),
                "SeatRow"      => new PdfString( $seat_state->seat_row ),
                "SeatNumber"   => new PdfNumber( $seat_state->seat_number ),
                "Price"        => $price_PDF_object,
                "PaymentState" => new PdfString( $this->process->payment_state ),
                "ProcessId"    => new PdfNumber( $this->process->id )
            ] ) );

            // Content stream for adding the seating plan
            $page->addContentStream( $seating_plan_start_content_stream->getContentStream() );

            if ( $seating_plan_config !== null ) {
                // Content Stream for colored marking of the seats and entrance
                $seating_plan_seats_content_stream = GenerateContentStream::generateNew( $seating_plan_start_content_stream->getLastGraphicsStateStack(), $seating_plan_start_content_stream->getContentStream()->getResourceDictionary(), $pdfFile );
                foreach ( $seat_states as $seat ) {
                    if ( $seat_state->show_id === $seat->show_id ) {
                        if ( $seat !== $seat_state ) {
                            $this->draw_seat( $seatLightColoredOperator, $seat, $seating_plan_seats_content_stream );
                        } else {
                            $this->draw_seat( $seatColoredOperator, $seat, $seating_plan_seats_content_stream );
                            if ( isset( $seat->eingang ) ) {
                                $this->draw_entrance( $seat->eingang, $seating_plan_scale, $seating_plan_seats_content_stream );
                            }
                        }
                    }
                }
                $page->addContentStream( $seating_plan_seats_content_stream->getContentStream() );

                // Content stream for finalizing the seating plan
                $page->addContentStream( $seating_plan_end_content_stream->getContentStream() );

                // Content Stream for text modules
                $text_modules_content_stream = GenerateContentStream::generateNew( $seating_plan_end_content_stream->getLastGraphicsStateStack(), $seating_plan_end_content_stream->getContentStream()->getResourceDictionary(), $pdfFile );
            } else {
                $text_modules_content_stream = GenerateContentStream::generateNew( $seating_plan_start_content_stream->getLastGraphicsStateStack(), $seating_plan_start_content_stream->getContentStream()->getResourceDictionary(), $pdfFile );
            }

            // Textbausteine
            $text_modules_content_stream->addOperator( new BeginTextObjectOperator() );
            if ( isset( $this->ticket_config->date_text_config ) ) {
                $this->addText( $this->ticket_config->date_text_config, $date_text_font_name, $text_modules_content_stream, date( "d.m.Y", strtotime( $show->date ) ) );
            }
            if ( isset( $this->ticket_config->time_text_config ) ) {
                $this->addText( $this->ticket_config->time_text_config, $time_text_font_name, $text_modules_content_stream, $show->time );
            }
            if ( isset( $this->ticket_config->seat_block_text_config ) ) {
                $this->addText( $this->ticket_config->seat_block_text_config, $seat_block_text_font_name, $text_modules_content_stream, $seat_state->seat_block );
            }
            if ( isset( $this->ticket_config->seat_row_text_config ) ) {
                $this->addText( $this->ticket_config->seat_row_text_config, $seat_row_text_font_name, $text_modules_content_stream, $seat_state->seat_row );
            }
            if ( isset( $this->ticket_config->seat_number_text_config ) ) {
                $this->addText( $this->ticket_config->seat_number_text_config, $seat_number_text_font_name, $text_modules_content_stream, (string) $seat_state->seat_number );
            }
            if ( isset( $this->ticket_config->price_text_config ) ) {
                if ( $price_PDF_object instanceof PdfNumber ) {
                    $price_text = number_format( $price_PDF_object->getValue(), 2, ",", "." ) . "€";
                } else {
                    $price_text = $price_PDF_object->getValue();
                }
                $this->addText( $this->ticket_config->price_text_config, $price_text_font_name, $text_modules_content_stream, $price_text );
            }
            if ( isset( $this->ticket_config->payment_state_text_config ) ) {
                $payment_state_text = match ( $this->process->payment_state ) {
                    Process::PAYMENT_STATE_OPEN => __( "open", "kjg-ticketing" ),
                    Process::PAYMENT_STATE_PAID => __( "paid", "kjg-ticketing" ),
                    Process::PAYMENT_STATE_BOX_OFFICE => __( "pays at box office", "kjg-ticketing" ),
                    default => $this->process->payment_state,
                };
                $this->addText( $this->ticket_config->payment_state_text_config, $payment_state_text_font_name, $text_modules_content_stream, $payment_state_text );
            }
            if ( isset( $this->ticket_config->process_id_text_config ) ) {
                $process_id_string = str_pad( (string) $this->process->id, 9, "0", STR_PAD_LEFT );
                $this->addText( $this->ticket_config->process_id_text_config, $process_id_text_font_name, $text_modules_content_stream, substr( $process_id_string, 0, 3 ) . " " . substr( $process_id_string, 3, 3 ) . " " . substr( $process_id_string, 6, 3 ) );
            }
            $text_modules_content_stream->addOperator( new EndTextObjectOperator() );
            // QR-Code is generated along with the text modules
            if ( isset( $this->ticket_config->qr_code_config ) ) {
                $text_modules_content_stream->addOperator( new PushGraphicsStateOperator() );
                $text_modules_content_stream->addOperator( new ModifyTransformationMatrixOperator( $qrCodeTransformationMatrix ) );
                $text_modules_content_stream->addOperator(
                    InlineImageOperator::getFromQRCode(
                        \QRcode::text( json_encode( [
                            "date"          => $show->date,
                            "time"          => $show->time,
                            "seat_block"    => $seat_state->seat_block,
                            "seat_row"      => $seat_state->seat_row,
                            "seat_number"   => $seat_state->seat_number,
                            "price"         => $price_PDF_object->getValue(),
                            "payment_state" => $this->process->payment_state,
                            "process_id"    => $this->process->id
                        ] ), false, QR_ECLEVEL_M, 1, 0 )
                    )
                );
                $text_modules_content_stream->addOperator( new PopGraphicsStateOperator() );
            }
            $page->addContentStream( $text_modules_content_stream->getContentStream() );

            // Add page to PDF document
            $pdfDocument->getPageList()->addPage( $page );
        }


        // ---------------
        // | Save ticket |
        // ---------------
        $this->generatedTicket = $pdfDocument->generatePdfFile( $this->allowDataCompression );
    }

    /**
     * Returns the name with which a font can be found in the ResourceDictionary.
     * If a font is not inside the ResourceDictionary, but part of the Standard 14 fonts, it will be added to the ResourceDictionary.
     *
     * @param TicketTextConfig|TicketSeatingPlanConfig $textConfig Configuration for a text module
     * @param ContentStream $templateContentStream ContentStream, in whose ResourceDictionary we are looking for the font.
     *
     * @return string|null Name with which the font can be found in the ResourceDictionary.
     * @throws \Exception When the font is neither inside the ResourceDictionary nor part of the standard 14 fonts
     */
    private function getFontName( TicketTextConfig|TicketSeatingPlanConfig $textConfig, ContentStream $templateContentStream ): ?string {
        $fontName = $templateContentStream->getResourceDictionary()->getFontNameByBaseName( $textConfig->font );
        if ( $fontName === null ) {
            $font     = Font::getStandard14Font( $textConfig->font, $templateContentStream->getPdfFile() );
            $fontName = $templateContentStream->getResourceDictionary()->addFont( $font );
        }

        return $fontName;
    }

    /**
     * Creates an Image XObject from a PNG file, adds it to the Resource Dictionary of the Content Stream, and returns the operator that will render the image.
     *
     * @param string $pngFile Path to the PNG file
     * @param ContentStream $templateContentStream ContentStream where the PNG file should be added to the Resource Dictionary
     *
     * @return ExternalObjectOperator Operator which renders the image, can be put into the Content Stream
     * @throws \Exception When the PNG file cannot be read or converted
     */
    private function getImageOperator( string $pngFile, ContentStream $templateContentStream ): ExternalObjectOperator {
        $xObject     = XObjectImage::createFromPNG( $pngFile, $templateContentStream->getPdfFile() );
        $xObjectName = new PdfName( $templateContentStream->getResourceDictionary()->addXObject( $xObject ) );

        return new ExternalObjectOperator( $xObjectName, $xObject );
    }

    /**
     * Draws a seat into the seating plan
     *
     * @param ExternalObjectOperator $seatOperator Operator which draws an XObject
     * @param Seat $seat
     * @param GenerateContentStream $contentStream ContentStream which is currently responsible for rendering the seating plan
     *
     * @throws \Exception In theory, it cannot be thrown
     */
    private function draw_seat( ExternalObjectOperator $seatOperator, $seat, GenerateContentStream $contentStream ): void {
        $contentStream->addOperator( new PushGraphicsStateOperator() );
        $transformationMatrix = TransformationMatrix::translation( $seat->position_x, $this->event->seating_plan_length - $seat->position_y );
        $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::rotation( deg2rad( - $seat->rotation ) ) );
        $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::scaling( $seat->width, $seat->length ) );
        $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::translation( - .5, - .5 ) );
        $contentStream->addOperator( new ModifyTransformationMatrixOperator( $transformationMatrix ) );
        $contentStream->addOperator( $seatOperator );
        $contentStream->addOperator( new PopGraphicsStateOperator() );
    }

    /**
     * Draws a seating plan area (without text) into the seating plan
     *
     * @param SeatingPlanArea $seating_plan_area
     * @param GenerateContentStream $contentStream ContentStream which is currently responsible for rendering the seating plan
     *
     * @throws \Exception In theory, it cannot be thrown
     */
    private function draw_seating_plan_area( SeatingPlanArea $seating_plan_area, GenerateContentStream $contentStream ) {
        $color = ColorRGB::fromHex( $seating_plan_area->color );
        if ( $contentStream->getLastGraphicsStateStack()->getGraphicsState()->getColorFilling() != $color ) {
            $contentStream->addOperator( new ColorRGBFillingOperator( $color ) );
        }

        $contentStream->addOperator( new PathRectangleOperator(
            new PdfNumber( $seating_plan_area->position_x ),
            new PdfNumber( $this->event->seating_plan_length - $seating_plan_area->position_y - $seating_plan_area->length ),
            new PdfNumber( $seating_plan_area->width ),
            new PdfNumber( $seating_plan_area->length )
        ) );
        $contentStream->addOperator( new FillPathNonzeroOperator() );
    }

    /**
     * Renders a text into the seating plan, centered on the with $x and $y defined point.
     * It is assumed that a TextObject is started previously with the BeginTextOperator.
     *
     * @param string $text Text to draw
     * @param float $x X-Position, where the text should be drawn
     * @param float $y Y-Position, where the text should be drawn
     * @param ColorRGB $color Color in which the text should be drawn
     * @param float $seating_plan_scale Factor, by which the seating plan was scaled to match the seating plan length units with the PDF's Device Space.
     * @param GenerateContentStream $contentStream ContentStream which is currently responsible for rendering the seating plan
     *
     * @throws \Exception If the text cannot be drawn
     */
    private function draw_text( string $text, float $x, float $y, ColorRGB $color, float $seating_plan_scale, GenerateContentStream $contentStream ): void {
        $graphicsState = $contentStream->getLastGraphicsStateStack()->getGraphicsState();
        if ( $graphicsState->getColorFilling() != $color ) {
            $contentStream->addOperator( new ColorRGBFillingOperator( $color ) );
        }

        $font     = $graphicsState->getTextState()->getTextFont();
        $fontSize = $graphicsState->getTextState()->getTextFontSize();

        $textOperator = new TextOperator( new PdfString( $font->fromUTF8( $text ) ) );
        $textOperator->calculateText( $graphicsState );
        $textLength = ( $textOperator->getEndPos()->x - $textOperator->getStartPos()->x ) / $seating_plan_scale;
        $contentStream->addOperator( new TextMatrixOperator(
            TransformationMatrix::translation( $x - $textLength * 0.5, $this->event->seating_plan_length - $y - $fontSize->getValue() * 0.3 )
        ) );
        $contentStream->addOperator( $textOperator );
    }

    /**
     * Draws the entrance into the seating plan, through which it is best to reach the seat.
     * If multiple entrances are connected, they are drawn recursively.
     *
     * @param int $entrance_id
     * @param float $seating_plan_scale Factor, by which the seating plan was scaled to match the seating plan length units with the PDF's Device Space.
     * @param GenerateContentStream $contentStream ContentStream which is currently responsible for rendering the seating plan
     *
     * @throws \Exception If the entrance cannot be drawn
     */
    private function draw_entrance( int $entrance_id, float $seating_plan_scale, GenerateContentStream $contentStream ): void {
        // Find entrance
        $entrance = null;
        foreach ( $this->entrances as $e ) {
            if ( $e->id === $entrance_id ) {
                $entrance = $e;
                break;
            }
        }
        if ( $entrance === null ) {
            return;
        }

        // Draw entrance
        $color = new ColorRGB( 1, 0, 0 );
        if ( $contentStream->getLastGraphicsStateStack()->getGraphicsState()->getColorStroking() != $color ) {
            $contentStream->addOperator( new ColorRGBStrokingOperator( $color ) );
        }
        // Arrow at the end of the entrance
        $endPoint      = new Point( $entrance->x3, $this->event->seating_plan_length - $entrance->y3 );
        $previousPoint = new Point( $entrance->x2, $this->event->seating_plan_length - $entrance->y2 );
        if ( $previousPoint == $endPoint ) {
            $previousPoint = new Point( $entrance->x1, $this->event->seating_plan_length - $entrance->y1 );
            if ( $previousPoint == $endPoint ) {
                $previousPoint = new Point( $entrance->x0, $this->event->seating_plan_length - $entrance->y0 );
            }
        }
        if ( $endPoint != $previousPoint ) {
            $distance = $endPoint->distanceTo( $previousPoint );
            $dx       = ( $previousPoint->x - $endPoint->x ) / $distance / 2;
            $dy       = ( $previousPoint->y - $endPoint->y ) / $distance / 2;
            $contentStream->addOperator( new PathBeginOperator( new PdfNumber( $endPoint->x + $dx * 0.5 + $dy * 0.5 ), new PdfNumber( $endPoint->y + $dy * 0.5 - $dx * 0.5 ) ) );
            $contentStream->addOperator( new PathLineOperator( new PdfNumber( $endPoint->x ), new PdfNumber( $endPoint->y ) ) );
            $contentStream->addOperator( new PathLineOperator( new PdfNumber( $endPoint->x + $dx * 0.5 - $dy * 0.5 ), new PdfNumber( $endPoint->y + $dy * 0.5 + $dx * 0.5 ) ) );
        }
        $contentStream->addOperator( new PathBeginOperator( new PdfNumber( $entrance->x3 ), new PdfNumber( $this->event->seating_plan_length - $entrance->y3 ) ) );
        $contentStream->addOperator( new PathBezierOperator(
            new PdfNumber( $entrance->x2 ),
            new PdfNumber( $this->event->seating_plan_length - $entrance->y2 ),
            new PdfNumber( $entrance->x1 ),
            new PdfNumber( $this->event->seating_plan_length - $entrance->y1 ),
            new PdfNumber( $entrance->x0 ),
            new PdfNumber( $this->event->seating_plan_length - $entrance->y0 )
        ) );
        // StrokePathOperator is not yet drawn just for the case that the path is connected with the previous entrance

        // Draw entrance that you shall walk through earlier
        if ( isset( $entrance->entrance_id ) ) {
            if ( $this->ticket_config->seating_plan_config->connect_entrance_arrows ) {
                $this->draw_entrance_connected( $entrance, $seating_plan_scale, $contentStream );
            } else {
                $contentStream->addOperator( new StrokePathOperator() );
                $this->draw_entrance( $entrance->entrance_id, $seating_plan_scale, $contentStream );
            }
        } else {
            $contentStream->addOperator( new StrokePathOperator() );
        }

        // Text for the entrance (if available)
        if ( isset( $entrance->text ) ) {
            $contentStream->addOperator( new BeginTextObjectOperator() );
            $this->draw_text( $entrance->text, $entrance->text_position_x, $entrance->text_position_y, new ColorRGB( 0, 0, 0 ), $seating_plan_scale, $contentStream );
            $contentStream->addOperator( new EndTextObjectOperator() );
        }
    }

    /**
     * Draws the entrance into the seating plan, which is connected to the consecutive entrance and therefore generates a long line, through which it is best to reach the seat.
     * If multiple entrances are connected, they are drawn recursively.
     *
     * @param Entrance $next_entrance In arrow direction consecutive entrance, with which this entrance should form a long, connected line
     * @param float $seating_plan_scale Factor, by which the seating plan was scaled to match the seating plan length units with the PDF's Device Space.
     * @param GenerateContentStream $contentStream ContentStream which is currently responsible for rendering the seating plan
     *
     * @throws \Exception If the entrance cannot be drawn
     */
    private function draw_entrance_connected( $next_entrance, float $seating_plan_scale, GenerateContentStream $contentStream ) {
        // finde aktuellen Eingang
        $entrance = null;
        foreach ( $this->entrances as $e ) {
            if ( $e->id === $next_entrance->entrance_id ) {
                $entrance = $e;
                break;
            }
        }
        if ( $entrance === null ) {
            $contentStream->addOperator( new StrokePathOperator() );

            return;
        }

        // Draw line connecting both entrances
        $x2 = $next_entrance->x0 * 2.0 - $next_entrance->x1;
        $y2 = $next_entrance->y0 * 2.0 - $next_entrance->y1;
        $x1 = $entrance->x3 * 2.0 - $entrance->x2;
        $y1 = $entrance->y3 * 2.0 - $entrance->y2;
        $contentStream->addOperator( new PathBezierOperator(
            new PdfNumber( $x2 ),
            new PdfNumber( $this->event->seating_plan_length - $y2 ),
            new PdfNumber( $x1 ),
            new PdfNumber( $this->event->seating_plan_length - $y1 ),
            new PdfNumber( $entrance->x3 ),
            new PdfNumber( $this->event->seating_plan_length - $entrance->y3 )
        ) );

        // Draw entrance
        $contentStream->addOperator( new PathBezierOperator(
            new PdfNumber( $entrance->x2 ),
            new PdfNumber( $this->event->seating_plan_length - $entrance->y2 ),
            new PdfNumber( $entrance->x1 ),
            new PdfNumber( $this->event->seating_plan_length - $entrance->y1 ),
            new PdfNumber( $entrance->x0 ),
            new PdfNumber( $this->event->seating_plan_length - $entrance->y0 )
        ) );
        // StrokePathOperator is not yet drawn just for the case that the path is connected with the previous entrance

        // Draw entrance that you shall walk through earlier
        if ( isset( $entrance->eingang ) ) {
            $this->draw_entrance_connected( $entrance, $seating_plan_scale, $contentStream );
        } else {
            $contentStream->addOperator( new StrokePathOperator() );
        }

        // Text for the entrance (if available)
        if ( isset( $entrance->text ) ) {
            $contentStream->addOperator( new BeginTextObjectOperator() );
            $this->draw_text( $entrance->text, $entrance->text_position_x, $entrance->text_position_y, new ColorRGB( 0, 0, 0 ), $seating_plan_scale, $contentStream );
            $contentStream->addOperator( new EndTextObjectOperator() );
        }
    }

    /**
     * Adds a text module.
     * It is assumed that a TextObject is started previously with the BeginTextOperator.
     *
     * @param TicketTextConfig $textConfig
     * @param string $fontName Name of the font in the ResourceDictionary of the $targetContentStream
     * @param GenerateContentStream $targetContentStream ContentStream to which the necessary Operators should be added
     * @param string $text Text that should be added
     *
     * @throws \Exception If the text cannot be added
     */
    private function addText( TicketTextConfig $textConfig, string $fontName, GenerateContentStream $targetContentStream, string $text ): void {
        $font             = $targetContentStream->getContentStream()->getResourceDictionary()->getFont( $fontName );
        $currentTextState = $targetContentStream->getLastGraphicsStateStack()->getGraphicsState()->getTextState();
        if ( $currentTextState->getTextFont() !== $font || $currentTextState->getTextFontSize()->getValue() !== $textConfig->font_size ) {
            $targetContentStream->addOperator( new TextFontOperator( new PdfName( $fontName ), $font, new PdfNumber( $textConfig->font_size ) ) );
        }

        $color = $textConfig->color->to_PDF_color();
        if ( $targetContentStream->getLastGraphicsStateStack()->getGraphicsState()->getColorFilling() != $color ) {
            $targetContentStream->addOperator( new ColorRGBFillingOperator( $color ) );
        }

        $textOperator = new TextOperator( new PdfString( $font->fromUTF8( $text ) ) );
        switch ( $textConfig->alignment ) {
            case TicketTextConfig::ALIGNMENT_LEFT:
                $targetContentStream->addOperator( new TextMatrixOperator(
                    TransformationMatrix::translation( $textConfig->position->x, $textConfig->position->y )
                ) );
                break;
            case TicketTextConfig::ALIGNMENT_CENTER:
                $textOperator->calculateText( $targetContentStream->getLastGraphicsStateStack()->getGraphicsState() );
                $textLength = $textOperator->getEndPos()->x - $textOperator->getStartPos()->x;
                $targetContentStream->addOperator( new TextMatrixOperator(
                    TransformationMatrix::translation( $textConfig->position->x - $textLength * 0.5, $textConfig->position->y )
                ) );
                break;
            case TicketTextConfig::ALIGNMENT_RIGHT:
                $textOperator->calculateText( $targetContentStream->getLastGraphicsStateStack()->getGraphicsState() );
                $textLength = $textOperator->getEndPos()->x - $textOperator->getStartPos()->x;
                $targetContentStream->addOperator( new TextMatrixOperator(
                    TransformationMatrix::translation( $textConfig->position->x - $textLength, $textConfig->position->y )
                ) );
                break;
        }
        $targetContentStream->addOperator( $textOperator );
    }

    /**
     * Returns a transformation matrix which transforms from the default User Space to an image coordinate system.
     * The image coordinate system lies inside the borders afterwards
     *
     * @param TicketImageConfig $imageConfig Configuration for QR Code or seating plan
     * @param float $targetWidth Width of the image coordinate system
     * @param float $targetHeight Height of the image coordinate system
     *
     * @return TransformationMatrix transformation matrix which transforms from the default User Space to an image coordinate system.
     */
    private function getTransformationMatrixForImageConfig( TicketImageConfig $imageConfig, float $targetWidth, float $targetHeight ): TransformationMatrix {
        // linke untere Ecke in Ursprung verschieben
        $transformationMatrix = TransformationMatrix::translation( $imageConfig->lower_left_corner->x, $imageConfig->lower_left_corner->y );
        // Rotation, sodass X-Achsen aufeinanderliegen
        $wx                   = $imageConfig->lower_right_corner->x - $imageConfig->lower_left_corner->x;
        $wy                   = $imageConfig->lower_right_corner->y - $imageConfig->lower_left_corner->y;
        $originalWidth        = sqrt( $wx * $wx + $wy * $wy );
        $hx                   = $imageConfig->upper_left_corner->x - $imageConfig->lower_left_corner->x;
        $hy                   = $imageConfig->upper_left_corner->y - $imageConfig->lower_left_corner->y;
        $originalHeight       = sqrt( $hx * $hx + $hy * $hy );
        $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::rotation( atan2( $wy, $wx ) ) );
        // Abschrägung der Y-Achse herausrechnen
        $newUpperLeftCorner   = $transformationMatrix->invers()->transformPoint( new Point( $imageConfig->upper_left_corner->x, $imageConfig->upper_left_corner->y ) );
        $transformationMatrix = $transformationMatrix->addTransformation( new TransformationMatrix( 1, 0, $newUpperLeftCorner->x / $newUpperLeftCorner->y, 1 ) );
        $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::scaling( 1, $newUpperLeftCorner->y / $originalHeight ) );
        // Skalieren und dabei Seitenverhältnis bewahren
        if ( $originalWidth / $originalHeight > $targetWidth / $targetHeight ) {
            $scaling              = $originalHeight / $targetHeight;
            $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::scaling( $scaling, $scaling ) );
            $xOffset              = ( $originalWidth / $scaling - $targetWidth ) / 2.0;
            $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::translation( $xOffset, 0 ) );
        } else {
            $scaling              = $originalWidth / $targetWidth;
            $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::scaling( $scaling, $scaling ) );
            $yOffset              = ( $originalHeight / $scaling - $targetHeight ) / 2.0;
            $transformationMatrix = $transformationMatrix->addTransformation( TransformationMatrix::translation( 0, $yOffset ) );
        }

        // skalieren auf passende Größe
        return $transformationMatrix;
    }


    /**
     * Speichert die Theaterkarten in der Vorgesehenen PDF-Datei
     * @throws \Exception
     * @see TicketGenerator::getTicketURL() URL, unter der die Theaterkarte gespeichert wurde
     * @deprecated Tickets are now generated on demand
     */
    public function saveTicket(): void {
        if ( $this->generatedTicket === null ) {
            $this->generateTicket();
        }

        global $ticketsFolder;
        $filePath     = $ticketsFolder . $this->getTicketName();
        $writtenBytes = @file_put_contents( $filePath, $this->generatedTicket );
        if ( $writtenBytes === false ) {
            throw new \Exception( "Failed to write PDF-File to {$filePath}" );
        }
    }

    /**
     * Returns the content of the PDF file, which can then be stored.
     */
    public function getTicketContent(): ?string {
        return $this->generatedTicket;
    }

    /**
     * Löscht die bereits existierende Theaterkarte, sofern eine in $vorgang gespeichert ist.
     * Es wird nur die Theaterkarte gelöscht, nicht aber der Eintrag in Vorgang
     *
     * @param string|null $theaterkarte URL der Theaterkarte. Wenn nicht angegeben, wird die URL aus dem im Konstruktor übergebenen $vorgang-Objekt genommen.
     *
     * @deprecated Tickets are now generated on demand
     */
    public function deleteExistingTicket( string $theaterkarte = null ) {
        if ( $theaterkarte === null ) {
            $theaterkarte = $this->process->theaterkarte;
        }

        global $ticketsFolder;
        if ( isset( $theaterkarte ) ) {
            $fileName = rawurldecode( substr( $theaterkarte, strrpos( $theaterkarte, "/" ) + 1 ) );
            if ( file_exists( $ticketsFolder . $fileName ) ) {
                unlink( $ticketsFolder . $fileName );
            }
        }
    }

    /**
     * Liefert die URL zurück, unter der die generierte Theaterkarte zu finden ist
     * @return string vollständige URL der Theaterkarte
     * @deprecated Tickets are now generated on demand
     */
    public function getTicketURL(): string {
        if ( $this->shows === null ) {
            $this->shows = $this->databaseConnection->get_shows();
        }

        $https = @$_SERVER["HTTPS"] !== null && $_SERVER["HTTPS"] !== "off";

        return ( $https ? "https://" : "http://" )
               . "{$_SERVER["HTTP_HOST"]}/"
               . "karten/"
               . rawurlencode( $this->getTicketName() );
    }

    /**
     * Returns the file name for the ticket
     * @return string
     */
    private function getTicketName(): string {
        return "Tickets_{$this->event->name}_{$this->process->id}.pdf";
    }

    /**
     * @param int $show_id
     *
     * @return Show
     * @throws \Exception When this function was called and $this->shows is still null
     */
    private function get_show( int $show_id ): Show {
        if ( $this->shows === null ) {
            throw new \Exception( "Shows were not yet loaded from database" );
        }
        foreach ( $this->shows as $show ) {
            if ( $show->id === $show_id ) {
                return $show;
            }
        }
        throw new \Exception( "Show with ID $show_id not found in database" );
    }
}
