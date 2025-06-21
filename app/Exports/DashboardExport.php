<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\PettyCashExpense;
use App\Models\InventoryMovement;
use App\Models\WasherPayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardExport implements WithMultipleSheets, Responsable
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
        $this->fileName = 'reporte_dashboard.xlsx';
    }

    public function sheets(): array
    {
        return [
            'Caja' => new class($this->start, $this->end) implements FromCollection, WithHeadings {
                use \Maatwebsite\Excel\Concerns\Exportable;
                private $start; private $end;
                public function __construct($s,$e){$this->start=$s;$this->end=$e;}
                public function collection()
                {
                    $tickets = Ticket::with('details')->where('canceled', false)
                        ->whereDate('created_at','>=',$this->start)
                        ->whereDate('created_at','<=',$this->end)->get();
                    $rows = [];
                    foreach($tickets as $t){
                        $rows[] = ['Ticket '.$t->id, $t->created_at->format('d/m/Y h:i A'), $t->total_amount];
                    }
                    $expenses = PettyCashExpense::whereDate('created_at','>=',$this->start)
                        ->whereDate('created_at','<=',$this->end)->get();
                    foreach($expenses as $e){
                        $rows[] = ['Gasto', $e->created_at->format('d/m/Y h:i A'), -$e->amount];
                    }
                    $payments = WasherPayment::whereDate('payment_date','>=',$this->start)
                        ->whereDate('payment_date','<=',$this->end)->get();
                    foreach($payments as $p){
                        $rows[] = ['Pago Lavador '.$p->washer->name, $p->payment_date->format('d/m/Y h:i A'), -$p->amount_paid];
                    }
                    return collect($rows);
                }
                public function headings(): array
                {
                    return ['Concepto','Fecha','Monto'];
                }
            },
            'Caja Chica' => new class($this->start, $this->end) implements FromCollection, WithHeadings {
                private $start; private $end;
                public function __construct($s,$e){$this->start=$s;$this->end=$e;}
                public function collection(){
                    return PettyCashExpense::whereDate('created_at','>=',$this->start)
                        ->whereDate('created_at','<=',$this->end)
                        ->select('description','created_at','amount')->get();
                }
                public function headings(): array
                { return ['Descripcion','Fecha','Monto']; }
            },
            'Inventario' => new class($this->start, $this->end) implements FromCollection, WithHeadings {
                private $start;private $end;
                public function __construct($s,$e){$this->start=$s;$this->end=$e;}
                public function collection(){
                    return InventoryMovement::with('product')
                        ->whereDate('created_at','>=',$this->start)
                        ->whereDate('created_at','<=',$this->end)
                        ->get()->map(function($m){return [
                            $m->product->name,
                            $m->movement_type,
                            $m->quantity,
                            $m->created_at->format('d/m/Y H:i')
                        ];});
                }
                public function headings(): array
                { return ['Producto','Tipo','Cantidad','Fecha']; }
            },
            'Lavadores' => new class($this->start,$this->end) implements FromCollection, WithHeadings {
                private $start;private $end;
                public function __construct($s,$e){$this->start=$s;$this->end=$e;}
                public function collection(){
                    return WasherPayment::with('washer')
                        ->whereDate('payment_date','>=',$this->start)
                        ->whereDate('payment_date','<=',$this->end)
                        ->get()->map(fn($p)=>[
                            $p->washer->name,
                            $p->payment_date->format('d/m/Y h:i A'),
                            $p->amount_paid
                        ]);
                }
                public function headings(): array
                { return ['Lavador','Fecha','Monto']; }
            },
        ];
    }
}
