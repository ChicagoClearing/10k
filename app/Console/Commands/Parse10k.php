<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Parse10k extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '10k:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads current list of 10k filings and parses
    full text document for keywords';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filings=\App\Filer::select('company_name','filename','10k.filing_id')
            ->whereRaw('filing_id not in (select 10k_id from forex)')
            ->groupBy('cik')
            ->orderBy('filing_date','ASC')
            ->skip('200')
            ->paginate(1000);

        $total_filings=$filings->count();

        // If there are no new documents alert console and end
        if($filings->count() < 0 ){
          $this->info('Nothing to Run');
          die;
        }

        // If there are new filings run parser
        elseif($total_filings > 0){

            foreach($filings as $filing){
                // Alert console that task is starting
                $this->info('loading '.$filing->company_name.'\'s latest 10k');

                // Get content from given url
                $content=@file_get_contents('https://www.sec.gov/Archives/'.$filing->filename);

                // Check for various terms and save accordingly
                if (strpos($content, 'Foreign Currency') !== false) {
                    $forex= new \App\Forex;
                    $forex->{'10k_id'}=$filing->filing_id;
                    $forex->search_term= 'Foreign Currency';
                }

                if (strpos($content, 'Foreign Exchange') !== false) {

                    $forex= new \App\Forex;
                    $forex->{'10k_id'}=$filing->filing_id;
                    $forex->search_term= 'Foreign Exchange';
                }

                // If term not found leave NULL
                else {
                    $forex= new \App\Forex;
                    $forex->{'10k_id'}=$filing->filing_id;
                    $forex->search_term=Null;
                }
                $forex->save();
                $this->info('Done: Parsed and logged');
            }
        }
    }
}
