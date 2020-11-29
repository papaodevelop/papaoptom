<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 27.08.2020
 * Time: 16:55
 */

namespace Dowell\Forsage\Helper;


use Magento\Framework\App\Helper\AbstractHelper;


use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Framework\Registry;

/**
 * Data Helper
 *
 * @package Webfresh\ThemeOptions\Helper
 */
class Additional extends AbstractHelper
{


    /**
     * Data constructor.
     *
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupColl
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {

        parent::__construct($context);

    }


    public function translite($value)
    {


        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v',
            'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

            'А' => 'A', 'Б' => 'B', 'В' => 'V',
            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'I', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        );
        $value = strtr($value, $converter);
        // в нижний регистр
        $str = strtolower($value);
        // заменям все ненужное нам на "-"
        $str = preg_replace('~[^-a-z0-9_]+~u', '_', $str);
        // удаляем начальные и конечные '-'
        $str = trim($str, "-");
        return $str;

    }
}