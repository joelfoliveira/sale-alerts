<?php


namespace SaleAlerts;


class BuildReport
{
    public function sendReport()
    {
        $emailSent = false;
        $products = Product::getBuildProducts();

        if(is_array($products) && count($products) > 0)
        {
            $emailHtml = $this->getBuildReportTemplate($products);

            if(!empty($emailHtml))
            {
                $emailSent = Utils::sendEmail($emailHtml, Config::$emailBuildReportSubject);
            }
        }

        return $emailSent;
    }

    private function getBuildReportTemplate($products)
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__DIR__).DIRECTORY_SEPARATOR.'views');
        $twig = new \Twig_Environment($loader);
        $template = $twig->loadTemplate('BuildReport.html');
        return $template->render(array('products' => $products));
    }
}