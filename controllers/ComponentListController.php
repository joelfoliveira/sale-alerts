<?php

namespace SaleAlerts;

class ComponentListController
{
    public function showComponentList()
    {
        $products = Product::getAllActive();
        $productsByCat = Product::organizeByCategory($products);

        $productsByCatName = $this->getProductsByCategoryWithCategoryNames($productsByCat);

        $buildProducts = Product::getBuildProducts();
        $total = Product::getTotal($buildProducts);

        echo $this->getComponentListTemplate($productsByCatName, $buildProducts, $total);
    }

    private function getProductsByCategoryWithCategoryNames($productsByCat)
    {
        $productsByCatName = array();

        $cats = ProductCategory::getAll();

        if(is_array($productsByCat) && count($productsByCat) > 0)
        {
            foreach($productsByCat as $catId => $products)
            {
                $catName = ProductCategory::getNameFromId($cats, $catId);
                $index = !empty($catName) ? $catName : $catId;
                $productsByCatName[$index] = $products;
            }
        }

        return $productsByCatName;
    }

    private function getComponentListTemplate($productsByCatName, $buildProducts, $total)
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__DIR__).DIRECTORY_SEPARATOR.'views');
        $twig = new \Twig_Environment($loader);
        $template = $twig->loadTemplate('ComponentsList.twig');
        return $template->render(array('productsByCatName' => $productsByCatName, 'buildProducts' => $buildProducts, 'total' => $total));
    }
}