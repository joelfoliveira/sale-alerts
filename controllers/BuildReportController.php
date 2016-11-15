<?php


namespace SaleAlerts;


class BuildReportController
{
    public function sendReport()
    {
        $emailSent = false;
        $products = Product::getBuildProducts();

        if(is_array($products) && count($products) > 0)
        {
            $lastBuildReport = BuildReport::getLast();

            $newBuildReport = $this->generateBuildReport($products);
            $newBuildReport->insert();

            $stats = BuildReportStats::getStats();
            if($stats == null){
                $stats = new BuildReportStats();
            }
            $stats->updateWithNewBuildReport($newBuildReport);

            $emailHtml = $this->getBuildReportTemplate($newBuildReport, $lastBuildReport, $stats);

            if(Config::$printEmail){
                echo $emailHtml;
            }

            if(Config::$sendEmail && !empty($emailHtml))
            {
                $emailSent = Utils::sendEmail($emailHtml, Config::$emailBuildReportSubject, Config::$emailBuildReportDestinations);
            }
        }

        return $emailSent;
    }

    private function generateBuildReport($products)
    {
        $br =  new BuildReport();
        $br->price = Product::getTotal($products);
        $br->date = time();
        $br->products = $products;
        return $br;
    }

    private function getBuildReportTemplate($newBuildReport, $lastBuildReport, $stats)
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__DIR__).DIRECTORY_SEPARATOR.'views');
        $twig = new \Twig_Environment($loader);
        $template = $twig->loadTemplate('BuildReport.twig');
        return $template->render(array('newBuildReport' => $newBuildReport, 'lastBuildReport' => $lastBuildReport, 'stats' => $stats));
    }
}