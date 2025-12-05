<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertaGenerada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $alerta;

    /**
     * Create a new event instance.
     */
    public function __construct($alerta)
    {
        $this->alerta = $alerta;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('alertas');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'nueva-alerta';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->alerta->id,
            'equipo' => $this->alerta->lectura->sensor->equipo->nombre ?? 'Desconocido',
            'mensaje' => $this->alerta->descripcion ?? $this->alerta->tipo_fallo,
            'nivel' => $this->alerta->nivel_criticidad,
            'timestamp' => $this->alerta->created_at->format('Y-m-d H:i:s')
        ];
    }
}
