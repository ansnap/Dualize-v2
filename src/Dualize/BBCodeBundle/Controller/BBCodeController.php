<?php

namespace Dualize\BBCodeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class BBCodeController extends Controller
{

    public function uploadImageAction(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        $tmp_file = $_FILES['image']['tmp_name'];

        $url = 'http://imageshack.us/upload_api.php';
        $key = '5ACEJKLT7a388c7eb69f1547742d4fc639f1adc1';
        $max_file_size = '5242880';
        $max_width = 600;
        $max_height = 600;

        $postData = array(
            'fileupload' => '@' . $tmp_file,
            'key' => $key,
            'max_file_size' => $max_file_size,
            'optimage' => 1,
            'optsize' => $max_width . 'x' . $max_height,
            'xml' => 'yes'
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $result = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($result);

        if (!$xml || isset($xml->error)) {
            return $response->setContent('Server error');
        } else {
            return $response->setContent($xml->links->image_link);
        }

        return $response->setContent('Wrong or empty request');
    }

    public function previewAction(Request $request)
    {
        $text = $request->request->get('text');

        return $this->render('DualizeBBCodeBundle:BBCode:preview.html.twig', array(
                    'text' => $text,
        ));
    }

}
