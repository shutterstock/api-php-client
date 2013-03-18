<?php

/**
 * This file contains examples of all the shutterstock api.
 */
 
//include Shutterstock API PHP Client lib
require_once('ShutterstockApiClient.php') ;

$sapi = new ShutterstockApiClient();

#Get category list
$categories = $sapi->getCategories();

#Search for images by keywords and various filters
$search_result = $sapi->search('dog');

#Get information about customer
$customer_info = $sapi->getCustomerUserInfo();

#Get list of all customer lightboxes
$lightboxes = $sapi->getLightboxes();

#Add new lightbox
$add_lightbox = $sapi->addLightbox('ssapi_test'.rand(0, 9999)); 

#Add image to particular lightbox
$add_lightbox_image = $sapi->addImageToLightbox($add_lightbox['lightbox_id'], 113021647);

#Remove image from particular lightbox
$remove_lightbox_image = $sapi->deleteImageFromLightbox($add_lightbox['lightbox_id'], 113021647);

#Get all images from particular lightbox
$lightbox_details = $sapi->getLightbox($add_lightbox['lightbox_id']);

#Delete particular lightbox
$delete_lightbox = $sapi->deleteLightbox($add_lightbox['lightbox_id']);

#Get list of all subscriptions for loggedin customer
$subscription = $sapi->getSubscriptions();

#Get download history for loggedin customer
$downloads = $sapi->getDownloads();

#Download an image using a subscription
$download = $sapi->downloadImage(113021647, 12345678, 'small', 'jpg');