<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\UnmappedCourierArticle;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkArticleNumberController extends Controller
{
    private function bucket(Request $request): array { return $request->session()->get('article_bucket', []); }
    private function address(Order $o): string { return collect([$o->house_building,$o->street,$o->area,$o->city,$o->district,$o->state,$o->pin_code])->filter()->implode(', '); }

    public function index(Request $request)
    {
        $today = now('Asia/Kolkata')->toDateString();
        $start = $request->input('start_date', $today); $end = $request->input('end_date', $today);
        $q = Order::with('shipment')->where('payment_status','paid')->whereIn('order_status',['confirmed','processing','packed','shipped','delivered']);
        $q->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '>=', $start)->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '<=', $end);
        if ($request->filled('client_name')) $q->where('customer_name','like','%'.$request->client_name.'%');
        if ($request->input('tracking_status','not_yet') === 'not_yet') $q->whereDoesntHave('shipment')->where(function($x){$x->whereNull('tracking_number')->orWhere('tracking_number','');});
        elseif ($request->input('tracking_status') === 'generated') $q->where(fn($x)=>$x->whereHas('shipment')->orWhereNotNull('tracking_number'));
        $orders = $q->orderByDesc(DB::raw('COALESCE(sale_date, created_at)'))->paginate(50)->withQueryString();
        $bucket = Order::whereIn('id',$this->bucket($request))->with('shipment')->get()->keyBy('id');
        return view('admin.bulk_article.index', compact('orders','bucket','start','end'));
    }

    public function proceed(Request $request)
    {
        $ids = array_map('intval', $request->input('order_ids', []));
        $valid = Order::whereIn('id',$ids)->where('payment_status','paid')->whereIn('order_status',['confirmed','processing','packed','shipped','delivered'])->whereDoesntHave('shipment')->whereNull('tracking_number')->pluck('id')->all();
        $bucket = array_values(array_unique(array_merge($this->bucket($request),$valid)));
        $request->session()->put('article_bucket',$bucket);
        return back()->with('success', count($valid).' eligible orders added to processing bucket.');
    }
    public function remove(Request $request, int $order) { $request->session()->put('article_bucket',array_values(array_diff($this->bucket($request),[$order]))); return back(); }
    public function uploadPage(Request $request)
    {
        $orders=Order::whereIn('id',$this->bucket($request))->get()->keyBy('id');
        abort_if($orders->isEmpty(), 400, 'Add orders to the processing bucket first.');
        return view('admin.bulk_article.upload',compact('orders'));
    }

    public function parse(Request $request)
    {
        $request->validate(['courier_file'=>'required|file|mimes:csv,txt,xlsx,xls|max:10240']);
        try {
            $spreadsheet=IOFactory::load($request->file('courier_file')->getRealPath());
            $rows=$spreadsheet->getActiveSheet()->toArray(null,true,true,false);
            $spreadsheet->disconnectWorksheets();
        } catch (\Throwable $e) {
            Log::error('Bulk article courier file could not be read', ['error'=>$e->getMessage()]);
            return redirect()->route('admin.bulk-article.upload')->withErrors(['courier_file'=>'The courier file could not be read: '.$e->getMessage()]);
        }
        if(count($rows)<2)return redirect()->route('admin.bulk-article.upload')->withErrors(['courier_file'=>'The courier file has no data rows.']);
        $headers=array_map(fn($v)=>trim((string)$v),array_shift($rows));
        // Rows go to a storage file, not the session: a large sheet can overflow a database session payload.
        $key=(string)Str::uuid();
        Storage::put("article-imports/{$key}.json",json_encode($rows));
        $request->session()->put('article_import',['headers'=>$headers,'key'=>$key]);
        return redirect()->route('admin.bulk-article.upload')->with('success','File loaded. Map its columns to continue.');
    }

    private function importRows(array $import): array
    {
        $json=Storage::get("article-imports/{$import['key']}.json");
        abort_if($json===null,422,'The uploaded file has expired. Please upload it again.');
        return json_decode($json,true)?:[];
    }

    /** Reads the mapped Excel rows, auto-connects same-name receivers to bucket orders and shows the connect screen. */
    public function match(Request $request)
    {
        $request->validate(['map'=>['required','array'],'map.receiver_name'=>'required|integer|min:0','map.receiver_address'=>'required|integer|min:0','map.article_number'=>'required|integer|min:0','map.courier_charge'=>'required|integer|min:0','map.pin_code'=>'nullable|integer|min:0','map.tracking_number'=>'nullable|integer|min:0','map.booking_date'=>'nullable|integer|min:0','map.receiver_phone'=>'nullable|integer|min:0']);
        $import=$request->session()->get('article_import'); abort_unless($import,422,'Upload a courier file first.');
        $map=$request->input('map'); $used=[];$rows=[];
        foreach($map as $column) if($column!==null && $column!=='' && (int)$column>=count($import['headers'])) abort(422,'A mapped column is outside the uploaded file.');
        foreach($this->importRows($import) as $index=>$row){$get=fn($key)=>isset($map[$key])&&$map[$key]!==null&&$map[$key]!==''?trim((string)($row[$map[$key]]??'')):'';$name=$get('receiver_name');$addr=$get('receiver_address');$pin=$get('pin_code');$article=$get('article_number');$track=$get('tracking_number')?:$article;$charge=$get('courier_charge');
            if(!$name&&!$addr&&!$article)continue;
            $issue=null;
            if(!$name||!$addr||!$article||!is_numeric($charge)||(float)$charge<0)$issue='Invalid';
            elseif(in_array(Str::lower($article),$used,true)||in_array(Str::lower($track),$used,true))$issue='Duplicate in file';
            elseif(OrderShipment::where('article_number',$article)->orWhere('tracking_number',$article)->orWhere('article_number',$track)->orWhere('tracking_number',$track)->exists())$issue='Already saved';
            if($article)$used[]=Str::lower($article);if($track)$used[]=Str::lower($track);
            $rows[$index]=['row'=>$index,'name'=>$name,'address'=>$addr,'pin'=>$pin,'article'=>$article,'tracking'=>$track,'charge'=>$charge,'booking_date'=>$this->bookingDate($get('booking_date')),'phone'=>$get('receiver_phone'),'issue'=>$issue];
        }
        // Every row repeating one value (e.g. "BUSINESS_PARCEL") means the article type column was mapped, not the article number.
        $articles=array_filter(array_column($rows,'article'));
        if(count($articles)>1&&count(array_unique(array_map('strtolower',$articles)))===1)
            return redirect()->route('admin.bulk-article.upload')->withErrors(['map'=>'Every row has the same Article Number ("'.reset($articles).'"). Select the column that holds the real article / barcode number.']);
        $orders=Order::whereIn('id',$this->bucket($request))->with(['shipment','items.product'])->get();
        $orders->each(fn($o)=>$o->setAttribute('website_weight',$this->weightForOrder($o)));
        Storage::put("article-imports/{$import['key']}-rows.json",json_encode($rows));
        return view('admin.bulk_article.connect',['rows'=>$rows,'orders'=>$orders,'pairs'=>$this->autoPairs($rows,$orders)]);
    }

    /** Same normalized name connects automatically; ties are broken by PIN / address overlap, and each order is used once. */
    private function autoPairs(array $rows, $orders): array
    {
        $open=$orders->filter(fn($o)=>!$o->shipment&&!$o->tracking_number);$pairs=[];$taken=[];
        foreach($rows as $index=>$r){
            if($r['issue'])continue;
            $candidates=$open->filter(fn($o)=>!isset($taken[$o->id])&&$this->normalize($o->customer_name)===$this->normalize($r['name']))
                ->map(fn($o)=>['order'=>$o,'score'=>$this->addressScore($r,$o)])->sortByDesc('score')->values();
            if($candidates->isEmpty())continue;
            if($candidates->count()>1&&$candidates[0]['score']<=$candidates[1]['score'])continue;
            $pairs[$index]=$candidates[0]['order']->id;$taken[$candidates[0]['order']->id]=true;
        }
        return $pairs;
    }

    private function addressScore(array $r, Order $o): float
    {
        $givenPin=$this->normalize($r['pin']);if($givenPin&&$this->normalize((string)$o->pin_code)===$givenPin)return 2;
        $a=array_unique(array_filter(preg_split('/[^a-z0-9]+/i',Str::lower($r['address']),-1,PREG_SPLIT_NO_EMPTY),fn($v)=>strlen($v)>2));
        $b=array_unique(array_filter(preg_split('/[^a-z0-9]+/i',Str::lower($this->address($o)),-1,PREG_SPLIT_NO_EMPTY),fn($v)=>strlen($v)>2));
        return count(array_intersect($a,$b))/max(1,min(count($a),count($b)));
    }

    /** Website weight in grams: each product's weight_kg times the ordered quantity. */
    private function weightForOrder(Order $order): float
    {
        return round($order->items->sum(fn($item)=>(float)($item->product?->weight_kg ?? 0)*(int)$item->quantity)*1000,2);
    }

    /** Courier booking date-time (e.g. "2026/10/26T13:45:09", "26-10-2026 13:45" or an Excel serial) as Y-m-d; this is the dispatch date, not the order date. */
    private function bookingDate(string $value): ?string
    {
        $value=trim($value);
        if($value==='')return null;
        if(preg_match('/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})/',$value,$m))[$y,$mo,$d]=[$m[1],$m[2],$m[3]];
        elseif(preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})/',$value,$m))[$y,$mo,$d]=[$m[3],$m[2],$m[1]];
        elseif(is_numeric($value)&&(float)$value>20000&&(float)$value<80000)return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)->format('Y-m-d');
        else return rescue(fn()=>\Illuminate\Support\Carbon::parse($value)->format('Y-m-d'),null,false);
        return checkdate((int)$mo,(int)$d,(int)$y)?sprintf('%04d-%02d-%02d',$y,$mo,$d):null;
    }

    private function normalize(string $value): string { return preg_replace('/[^a-z0-9]+/','',Str::lower(trim($value))); }

    /** Inserts shipments for the connected Excel rows; every other valid row is kept as "Not mapped from order". */
    public function save(Request $request)
    {
        $data=$request->validate(['pairs'=>'nullable|array','pairs.*'=>'required|integer']);$pairs=$data['pairs']??[];
        $import=$request->session()->get('article_import');
        $rows=$import?json_decode((string)Storage::get("article-imports/{$import['key']}-rows.json"),true):null;
        abort_unless($rows,422,'Upload and map a courier file first.');
        $bucket=$this->bucket($request);$saved=[];$savedRows=[];$skipped=0;$reserved=0;
        abort_if(count(array_unique($pairs))!==count($pairs),422,'An order can be connected to only one Excel row.');
        DB::transaction(function()use($pairs,$rows,$bucket,$request,&$saved,&$savedRows,&$skipped,&$reserved){
            foreach($pairs as $rowIndex=>$orderId){
                $r=$rows[$rowIndex]??null;
                if(!$r||$r['issue']||!in_array((int)$orderId,$bucket,true)){$skipped++;continue;}
                $order=Order::whereKey($orderId)->with('items.product')->lockForUpdate()->first();
                if(!$this->canReceiveShipment($order)||$this->articleTaken($r['article'],$r['tracking'])){$skipped++;continue;}
                $this->createShipment($order,$r,$request->user()->id);$saved[]=$order->id;$savedRows[]=(string)$rowIndex;
            }
            foreach($rows as $rowIndex=>$r){
                if($r['issue']||in_array((string)$rowIndex,$savedRows,true)||$this->articleTaken($r['article'],$r['tracking']))continue;
                $record=UnmappedCourierArticle::firstOrNew(['article_number'=>$r['article']]);
                if($record->exists&&$record->status===UnmappedCourierArticle::CONNECTED)continue;
                $record->fill(['tracking_number'=>$r['tracking'],'receiver_name'=>$r['name'],'receiver_address'=>$r['address'],'pin_code'=>$r['pin']?:null,'booking_date'=>($r['booking_date']??'')?:null,'receiver_phone'=>($r['phone']??'')?:null,'courier_charge'=>$r['charge'],'status'=>UnmappedCourierArticle::NOT_MAPPED,'created_by'=>$record->created_by??$request->user()->id])->save();$reserved++;
            }
        });
        Storage::delete(["article-imports/{$import['key']}.json","article-imports/{$import['key']}-rows.json"]);
        $request->session()->put('article_bucket',array_values(array_diff($bucket,$saved)));$request->session()->forget('article_import');
        return redirect()->route('admin.bulk-article.index')->with('success',count($saved).' shipment records saved.'.($skipped?" {$skipped} connection(s) skipped.":'').($reserved?" {$reserved} row(s) kept as Not mapped from order.":''));
    }

    private function canReceiveShipment(?Order $order): bool
    {
        return $order&&$order->payment_status==='paid'&&in_array($order->order_status,['confirmed','processing','packed','shipped','delivered'],true)&&!$order->shipment()->exists()&&!$order->tracking_number;
    }

    private function articleTaken(string $article, ?string $tracking): bool
    {
        $numbers=array_values(array_unique(array_filter([$article,$tracking])));
        return OrderShipment::whereIn('article_number',$numbers)->orWhereIn('tracking_number',$numbers)->exists();
    }

    /** $r holds article, tracking, name, address and charge; weight always comes from the website. */
    private function createShipment(Order $order, array $r, int $userId): OrderShipment
    {
        $shipment=OrderShipment::create(['order_id'=>$order->id,'article_number'=>$r['article'],'tracking_number'=>$r['tracking'],'courier_name'=>null,'receiver_name'=>$r['name'],'receiver_address'=>$r['address'],'weight'=>$this->weightForOrder($order->loadMissing('items.product')),'weight_unit'=>'gram','courier_charge'=>$r['charge'],'shipment_date'=>($r['booking_date']??null)?:now('Asia/Kolkata')->toDateString(),'shipment_status'=>'Generated','created_by'=>$userId]);
        DB::table('order_shipment_histories')->insert(['order_shipment_id'=>$shipment->id,'user_id'=>$userId,'old_values'=>null,'new_values'=>json_encode($shipment->getAttributes()),'created_at'=>now(),'updated_at'=>now()]);
        UnmappedCourierArticle::where('article_number',$r['article'])->update(['status'=>UnmappedCourierArticle::CONNECTED,'order_shipment_id'=>$shipment->id,'connected_by'=>$userId,'connected_at'=>now()]);
        return $shipment;
    }

    public function unmapped(Request $request)
    {
        // Only pending rows: once an article / tracking number is generated on any shipment it leaves this list.
        $q=UnmappedCourierArticle::where('status',UnmappedCourierArticle::NOT_MAPPED)
            ->whereNotExists(fn($s)=>$s->from('order_shipments')->where(fn($w)=>$w
                ->whereColumn('order_shipments.article_number','unmapped_courier_articles.article_number')
                ->orWhereColumn('order_shipments.tracking_number','unmapped_courier_articles.article_number')
                ->orWhereColumn('order_shipments.article_number','unmapped_courier_articles.tracking_number')
                ->orWhereColumn('order_shipments.tracking_number','unmapped_courier_articles.tracking_number')))
            ->orderByDesc('created_at');
        if($request->filled('search')){$like='%'.$request->search.'%';$q->where(fn($x)=>$x->where('receiver_name','like',$like)->orWhere('article_number','like',$like)->orWhere('tracking_number','like',$like)->orWhere('pin_code','like',$like)->orWhere('receiver_phone','like',$like));}
        $articles=$q->paginate(50)->withQueryString();
        $this->attachLikelyOrderDates($articles->getCollection());
        return view('admin.bulk_article.unmapped',compact('articles'));
    }

    /** Excel rows carry no order link, so the order date comes from the open order with the same phone, else same name and PIN. */
    private function attachLikelyOrderDates($articles): void
    {
        $digits=fn($v)=>substr(preg_replace('/\D+/','',(string)$v),-10);
        $phones=$articles->map(fn($a)=>$digits($a->receiver_phone))->filter(fn($p)=>strlen($p)===10)->unique()->values();
        $pins=$articles->pluck('pin_code')->filter()->unique()->values();
        if($phones->isEmpty()&&$pins->isEmpty())return;
        $orders=Order::where('payment_status','paid')->whereIn('order_status',['confirmed','processing','packed','shipped','delivered'])
            ->whereDoesntHave('shipment')->where(fn($x)=>$x->whereNull('tracking_number')->orWhere('tracking_number',''))
            ->where(fn($x)=>$x->when($phones->isNotEmpty(),fn($p)=>$p->orWhereIn(DB::raw("RIGHT(REPLACE(REPLACE(REPLACE(customer_phone, ' ', ''), '-', ''), '+', ''), 10)"),$phones))
                ->when($pins->isNotEmpty(),fn($p)=>$p->orWhereIn('pin_code',$pins)))
            ->orderByDesc(DB::raw('COALESCE(sale_date, created_at)'))->get(['order_number','customer_name','customer_phone','pin_code','sale_date','created_at']);
        foreach($articles as $a){
            $phone=$digits($a->receiver_phone);
            $order=(strlen($phone)===10?$orders->first(fn($o)=>$digits($o->customer_phone)===$phone):null)
                ??($a->pin_code?$orders->first(fn($o)=>$o->pin_code==$a->pin_code&&strcasecmp(trim($o->customer_name),trim($a->receiver_name))===0):null);
            $a->likely_order_number=$order?->order_number;
            $a->likely_order_date=$order?($order->sale_date??$order->created_at):null;
        }
    }

    public function editUnmapped(UnmappedCourierArticle $article)
    {
        abort_if($article->status===UnmappedCourierArticle::CONNECTED,409,'This article is already connected to an order.');
        return view('admin.bulk_article.unmapped_edit',compact('article'));
    }

    /** Orders that can still receive tracking, searched by customer name, order ID or phone, for the map screen. */
    public function searchOrders(Request $request)
    {
        $term=trim((string)$request->input('q',''));
        if(mb_strlen($term)<2)return response()->json(['orders'=>[]]);
        $like='%'.$term.'%';
        $orders=Order::with('items.product')->where('payment_status','paid')->whereIn('order_status',['confirmed','processing','packed','shipped','delivered'])
            ->whereDoesntHave('shipment')->where(fn($x)=>$x->whereNull('tracking_number')->orWhere('tracking_number',''))
            ->where(fn($x)=>$x->where('customer_name','like',$like)->orWhere('order_number','like',$like)->orWhere('pin_code','like',$like)
                ->when(strlen(preg_replace('/\D+/','',$term))>=4,fn($p)=>$p->orWhereRaw("REPLACE(REPLACE(REPLACE(customer_phone, ' ', ''), '-', ''), '+', '') LIKE ?",['%'.preg_replace('/\D+/','',$term).'%'])))
            ->orderByDesc(DB::raw('COALESCE(sale_date, created_at)'))->limit(30)->get();
        return response()->json(['orders'=>$orders->map(fn($o)=>[
            'order_number'=>$o->order_number,'name'=>$o->customer_name,'phone'=>$o->customer_phone,'address'=>$this->address($o),
            'date'=>optional($o->sale_date??$o->created_at)->format('d-m-Y'),'amount'=>number_format((float)$o->grand_total,2),'weight'=>$this->weightForOrder($o),
        ])]);
    }

    /** Connects a reserved Excel row to an order by its order ID (order number). */
    public function connectUnmapped(Request $request, UnmappedCourierArticle $article)
    {
        $data=$request->validate(['order_number'=>'required|string|max:255']);
        $order=DB::transaction(function()use($data,$article,$request){
            $article=UnmappedCourierArticle::whereKey($article->id)->lockForUpdate()->firstOrFail();
            if($article->status===UnmappedCourierArticle::CONNECTED)throw ValidationException::withMessages(['order_number'=>'This article is already connected to an order.']);
            $order=Order::where('order_number',trim($data['order_number']))->lockForUpdate()->first();
            if(!$order)throw ValidationException::withMessages(['order_number'=>'No order found with this order ID.']);
            if(!$this->canReceiveShipment($order))throw ValidationException::withMessages(['order_number'=>'This order is not paid / confirmed, or it already has tracking.']);
            if($this->articleTaken($article->article_number,$article->tracking_number))throw ValidationException::withMessages(['order_number'=>'This article number is already saved on another shipment.']);
            $this->createShipment($order,['article'=>$article->article_number,'tracking'=>$article->tracking_number,'name'=>$article->receiver_name,'address'=>$article->receiver_address,'charge'=>$article->courier_charge,'booking_date'=>$this->bookingDate((string)$article->booking_date)],$request->user()->id);
            return $order;
        });
        $request->session()->put('article_bucket',array_values(array_diff($this->bucket($request),[$order->id])));
        return redirect()->route('admin.bulk-article.unmapped')->with('success',"Article {$article->article_number} connected to order {$order->order_number}.");
    }

    public function destroyUnmapped(UnmappedCourierArticle $article)
    {
        abort_if($article->status===UnmappedCourierArticle::CONNECTED,409,'A connected article cannot be deleted here.');
        $article->delete();
        return redirect()->route('admin.bulk-article.unmapped')->with('success','Not mapped article deleted.');
    }

    public function history(Request $request)
    {
        $q=OrderShipment::with(['order.items.product.images','order.items.product.category','order.items.product.sizes','order.items.productSize','order.operations'])->orderByDesc('created_at');foreach(['article_number','tracking_number','courier_name'] as $field)if($request->filled($field))$q->where($field,'like','%'.$request->$field.'%');if($request->filled('client_name'))$q->where('receiver_name','like','%'.$request->client_name.'%');if($request->filled('order_id'))$q->whereHas('order',fn($o)=>$o->where('order_number','like','%'.$request->order_id.'%'));if($request->filled('status'))$q->where('shipment_status',$request->status);if($request->filled('start_date'))$q->whereDate('shipment_date','>=',$request->start_date);if($request->filled('end_date'))$q->whereDate('shipment_date','<=',$request->end_date);
        // With no filter values, show the complete saved shipment history by default.
        $shipments=$q->get();return view('admin.bulk_article.history',compact('shipments'));
    }
    public function edit(OrderShipment $shipment) { $shipment->load('order'); return view('admin.bulk_article.edit',compact('shipment')); }
    public function update(Request $request,OrderShipment $shipment)
    {
        $data=$request->validate(['article_number'=>'nullable|string|max:255|unique:order_shipments,article_number,'.$shipment->id,'tracking_number'=>'nullable|string|max:255|unique:order_shipments,tracking_number,'.$shipment->id,'courier_name'=>'nullable|string|max:255','weight'=>'nullable|numeric|min:0','weight_unit'=>'required|string|max:20','courier_charge'=>'nullable|numeric|min:0','shipment_date'=>'nullable|date','shipment_status'=>'required|string|max:100','receiver_name'=>'required|string|max:255','receiver_address'=>'required|string']);
        DB::transaction(function()use($shipment,$data,$request){$old=$shipment->getAttributes();$shipment->fill($data);$shipment->updated_by=$request->user()->id;$shipment->save();DB::table('order_shipment_histories')->insert(['order_shipment_id'=>$shipment->id,'user_id'=>$request->user()->id,'old_values'=>json_encode($old),'new_values'=>json_encode($shipment->getAttributes()),'created_at'=>now(),'updated_at'=>now()]);});return redirect()->route('admin.bulk-article.history')->with('success','Shipment updated.');
    }
    public function destroy(OrderShipment $shipment)
    {
        $this->deleteShipmentRecords([$shipment->id]);

        return redirect()->route('admin.bulk-article.history')->with('success', 'Tracking record deleted.');
    }
    public function bulkDestroy(Request $request)
    {
        $data = $request->validate(['shipment_ids' => 'required|array|min:1', 'shipment_ids.*' => 'required|integer|distinct|exists:order_shipments,id']);
        $deleted = $this->deleteShipmentRecords($data['shipment_ids']);

        return redirect()->route('admin.bulk-article.history')->with('success', $deleted . ' tracking record(s) deleted.');
    }
    private function deleteShipmentRecords(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $shipments = OrderShipment::whereIn('id', $ids)->lockForUpdate()->get();
            foreach ($shipments as $shipment) {
                $order = Order::whereKey($shipment->order_id)->lockForUpdate()->first();
                if ($order && in_array($order->tracking_number, array_filter([$shipment->article_number, $shipment->tracking_number]), true)) {
                    $order->tracking_number = null;
                    $order->save();
                }
                // Shipment history rows are removed by the foreign key cascade.
                $shipment->delete();
            }
            return $shipments->count();
        });
    }
    public function manual(Order $order) { return view('admin.bulk_article.manual',compact('order')); }
    public function storeManual(Request $request,Order $order)
    {
        abort_unless($order->payment_status==='paid'&&in_array($order->order_status,['confirmed','processing','packed','shipped','delivered']),403);
        $data=$request->validate(['article_number'=>'nullable|required_without:tracking_number|string|max:255|unique:order_shipments,article_number','tracking_number'=>'nullable|required_without:article_number|string|max:255|unique:order_shipments,tracking_number','courier_name'=>'nullable|string|max:255','weight'=>'nullable|numeric|min:0','weight_unit'=>'required|string|max:20','courier_charge'=>'nullable|numeric|min:0','shipment_date'=>'nullable|date','receiver_name'=>'required|string|max:255','receiver_address'=>'required|string']);
        abort_if($order->shipment||$order->tracking_number,409,'Tracking information already exists.');$s=OrderShipment::create($data+['order_id'=>$order->id,'shipment_status'=>'Generated','created_by'=>$request->user()->id]);DB::table('order_shipment_histories')->insert(['order_shipment_id'=>$s->id,'user_id'=>$request->user()->id,'old_values'=>null,'new_values'=>json_encode($s->getAttributes()),'created_at'=>now(),'updated_at'=>now()]);return redirect()->route('admin.orders.show',$order)->with('success','Shipment created.');
    }
}
