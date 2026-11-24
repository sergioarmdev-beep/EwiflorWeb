<?php

namespace Modules\ServicioTecnico\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Modules\ServicioTecnico\Support\VerificacionOtp;

class CodigoVerificacion extends Mailable
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public int $vigenciaMinutos = VerificacionOtp::VIGENCIA_MINUTOS,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu código de verificación de EwiflorWeb',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'serviciotecnico::publico.emails.codigo',
        );
    }
}
