<?php
/**
 * Created by PhpStorm.
 * User: aleksandar
 * Date: 6/6/18
 * Time: 6:07 PM
 */

namespace App\Controller;
use App\Form\IpType;
use App\Service\IpDetection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MainController
 *
 * Converts ips to cities.
 * @package App\Controller
 */
class MainController extends Controller
{
    /**
     * @Route("/", name="index")
     * @Template("UploadIp/Index.html.twig")
     * @return mixed
     */
    public function index()
    {
        $form = $this->createForm(IpType::class, null, [
            'action' => $this->generateUrl('uploadIps'),
            'method' => 'POST',
            'attr' => [
                'data-api-action' => $this->generateUrl('uploadIps'),
                'data-abide' => 'data-abide',
                'novalidate' => 'novalidate'
            ]
        ]);

        $templateVariables['itemForm'] = $form->createView();

        return $templateVariables;
    }

    /**
     * @Route("/upload/", name="uploadIps")
     * @Method("POST")
     * @param Request $request
     * @return string
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function createCsv(Request $request)
    {
        $csvFile = $request->files->get('ip')['ip'];
        $ips = file($csvFile);

        $originalName = 'Cities_' . $csvFile->getClientOriginalName();

        ob_start();
        ini_set('max_execution_time', 0);
        $fp = fopen('php://output' , 'a');

        foreach ($ips as $ip)
        {
            // Ignoring "Ip" title if exists...
            if (preg_match('/ip/i', $ip))
                continue;

            // Ignoring new lines if exists...
            $ip = str_replace("\n", '', $ip);
            $ip = str_replace("\r", '', $ip);

            // Collecting country data from user IP address...
            $country = $this->getCityName($ip);
            $city = $country['city'];
            $val = [$ip, $city, $country['country']['shortCode']];
            fputcsv($fp, $val);
        }

        fclose($fp);

        $response = new Response(ob_get_clean(), 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $originalName . '"'
        ]);

        return $response;
    }

    /**
     * @Route("/uploadAjax/", name="uploadIpsAjax")
     * @Method("POST")
     * @param Request $request
     * @return string
     */
    public function createCsvAjax(Request $request)
    {
        $csvFile = $request->files->get('file');
        $ips = file($csvFile);

        if (preg_match('/ip/i', $ips[0]))
            unset($ips[0]);

        return $this->json(json_encode($ips));
    }
    /**
     * @param $ip
     * @return array
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function getCityName($ip)
    {
        $databasePath = dirname($this->get('kernel')->getRootDir()) . DIRECTORY_SEPARATOR .
            'var' . DIRECTORY_SEPARATOR .
            'misc' . DIRECTORY_SEPARATOR .
            'GeoLite2-City.mmdb';

        // Translating user IP to Country name because of sku various price.
        $ipDetector = new IpDetection($databasePath);

        return $ipDetector->getCity($ip);
    }

    /**
     * @param $ip
     * @return array
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function getCountryName($ip)
    {
        $databasePath = dirname($this->get('kernel')->getRootDir()) . DIRECTORY_SEPARATOR .
            'var' . DIRECTORY_SEPARATOR .
            'misc' . DIRECTORY_SEPARATOR .
            'GeoLite2-Country.mmdb';

        // Translating user IP to Country name because of sku various price.
        $ipDetector = new IpDetection($databasePath);

        return $ipDetector->getCountry($ip);
    }
}