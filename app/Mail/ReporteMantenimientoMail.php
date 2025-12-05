<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReporteMantenimientoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $datosReporte;
    public $tipoReporte;
    public $destinatario;

    /**
     * Create a new message instance.
     */
    public function __construct(array $datosReporte, string $tipoReporte, $destinatario)
    {
        $this->datosReporte = $datosReporte;
        $this->tipoReporte = $tipoReporte;
        $this->destinatario = $destinatario;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tipoTexto = match($this->tipoReporte) {
            'diario' => 'Diario',
            'semanal' => 'Semanal',
            'mensual' => 'Mensual',
            default => 'General'
        };

        return new Envelope(
            subject: "📊 Reporte de Mantenimiento Predictivo - {$tipoTexto} - " . now()->format('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-mantenimiento',
            with: [
                'datos' => $this->datosReporte,
                'tipo' => $this->tipoReporte,
                'destinatario' => $this->destinatario,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
