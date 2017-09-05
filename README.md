### NOTE: This is currently a work in progress and is designed as just one example on how to interact with the Shutterstock API using PHP.

# Shutterstock API PHP Client

PHP Client is a single file library which provides an easy way to interact with the Shutterstock Inc. API <http://api.shutterstock.com>. You will need an API username and key
from Shutterstock with the appropriate permissions in order to use this client.

### Get Started:

Include "ShutterstockApiClient.php" in your PHP script and initialize class.

    require_once('ShutterstockApiClient.php');
	
    $sapi = new ShutterstockApiClient();
    
    **Note:** Make sure to replace below values in the class with your credentials.
    
    //customer credentials     
    private static $customer_username      = 'XXXXXX';
    private static $customer_password       = 'XXXXX';
    
    //basic auth info
    private static $auth_username = 'XXXXX';
    private static $auth_key      = 'XXXXX'; 

Below are some examples of how to use this client to interact with Shutterstock Inc. API:

### Get category list
    $categories = $sapi->getCategories();

### Search for images by keywords and various filters
    $search_result = $sapi->search('dog', $filters);
  
There are many filters supported while searching for Shutterstock images catalog. You need to pass these filters in $filters array as key-value pair.

<table>
    		<tbody>
				<tr class="paramtable">						
  					<td style="width:20%"><b>all</b></td>
					<td style="width:10%">boolean</td>
  					<td style="width:55%">
						A flag indicating that all search results should be returned. Accepted values are 1 for true and 0 for false.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>category_id</b></td>
					<td style="width:10%">int</td>
  					<td style="width:55%">
						An integer category id to search within. Required when searchterm, photographer_name, submitter_id, and color are not provided.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>color</b></td>
					<td style="width:10%">html color</td>
  					<td style="width:55%">
						A color to search for. Required when searchterm, category_id, photographer_name, and submitter_id are not provided. Accepted values are 6-character HTML hex colors from '000000' to 'FFFFFF', with or without number sign.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>commercial_only</b></td>
					<td style="width:10%">boolean</td>
  					<td style="width:55%">
						A flag indicating that only images which are available for commercial use should be returned; no images for editorial use only. Accepted values are 1 for true and 0 for false.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>enhanced_only</b></td>
					<td style="width:10%">boolean</td>
  					<td style="width:55%">
						A flag indicating that only images with enhanced licenses available should be returned. Accepted values are 1 for true and 0 for false.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>exclude_keywords</b></td>
					<td style="width:10%">str</td>
  					<td style="width:55%">
						A string of space-separated keywords to exclude from your search.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>language</b></td>
					<td style="width:10%">language</td>
  					<td style="width:55%">
						The 2-letter language code that the query and desired results should be in.  If not specified, use an eligible language from the Accept-Language header.  Otherwise, defaults to "en". Accepted values are: 'en', 'zh', 'nl', 'fr', 'de', 'it', 'jp', 'pt', 'ru', 'es', 'cs', 'hu', 'tr', 'pl'.
							Default is 'en'.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>model_released</b></td>
					<td style="width:10%">boolean</td>
  					<td style="width:55%">
						A flag indicating that only model-released photos should be returned. Accepted values are 1 for true and 0 for false.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>orientation</b></td>
					<td style="width:10%">enum</td>
  					<td style="width:55%">
						Whether a photograph is wider than it is tall, or taller than it is wide. Accepted values are: 'all', 'horizontal', 'vertical'.
							Default is 'all'.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>page_number</b></td>
					<td style="width:10%">nonnegative integer</td>
  					<td style="width:55%">
						Which page of results is wanted Accepted values are nonnegative integers.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>photographer_name</b></td>
					<td style="width:10%">str</td>
  					<td style="width:55%">
						The username of a specific submitter whose work you want to search within. Required when searchterm, category_id, submitter_id, and color are not provided.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>safesearch</b></td>
					<td style="width:10%">boolean</td>
  					<td style="width:55%">
						A flag indicating that only images suitable for all ages should be returned. Accepted values are 1 for true and 0 for false.
							Default is '1'.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>search_group</b></td>
					<td style="width:10%">enum</td>
  					<td style="width:55%">
						A media type to search within. Accepted values are: 'photos', 'illustrations', 'vectors', 'all'.
							Default is 'all'.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>searchterm</b></td>
					<td style="width:10%">str</td>
  					<td style="width:55%">
						A string of space-separated keywords to search for. Required when category_id, photographer_name, submitter_id, and color are not provided. Example: 'cat'.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>sort_method</b></td>
					<td style="width:10%">enum</td>
  					<td style="width:55%">
						How the results should be sorted. Accepted values are: 'newest', 'oldest', 'popular', 'random', 'relevance'.
							Default is 'popular'.
					</td>
				</tr>
				<tr class="paramtable">
  					<td style="width:20%"><b>submitter_id</b></td>
					<td style="width:10%">int</td>
  					<td style="width:55%">
						The integer id of a specific submitter whose work you want to search within. Required when searchterm, category_id, photographer_name, and color are not provided.
					</td>
				</tr>
		</tbody></table>


### Get information about customer
    $customer_info = $sapi->getCustomerUserInfo();

### Get list of all customer lightboxes
    $lightboxes = $sapi->getLightboxes();

### Add new lightbox
    $add_lightbox = $sapi->addLightbox(lightbox_name); 

### Add image to particular lightbox
    $add_lightbox_image = $sapi->addImageToLightbox(lightbox_id, image_id);

### Remove image from particular lightbox
    $remove_lightbox_image = $sapi->deleteImageFromLightbox(lightbox_id, image_id);

### Get all images from particular lightbox
    $lightbox_details = $sapi->getLightbox(lightbox_id);

### Delete particular lightbox
    $delete_lightbox = $sapi->deleteLightbox(lightbox_id);

### Get list of all subscriptions for loggedin customer
    $subscription = $sapi->getSubscriptions();

### Get download history for loggedin customer
    $downloads = $sapi->getDownloads();

### Download an image using a subscription
    $download = $sapi->downloadImage(image_id, subscription_id, size, format, meta_data);

## License

[MIT](LICENSE) © 2017 Shutterstock Images, LLC
