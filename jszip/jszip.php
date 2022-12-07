<?php
/**
 * Plugin Name: Jszip
 * Description: Permite bajar todas las imagenes de una entrada comprimidos en zip con un boton de descarga.
 * Author: revan20
 * Version: 0.1
 */

 function jszip_scripts() {
    if (is_single()) {
    	wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js',  array(), '1.12.4', true);
    	wp_enqueue_script( 'jszip', 'https://stuk.github.io/jszip/dist/jszip.js',  array(), '', false);
    	wp_enqueue_script( 'filesaver', 'https://stuk.github.io/jszip/vendor/FileSaver.js',  array(), '', false);
    }
}
add_action( 'wp_enqueue_scripts', 'jszip_scripts' );

// do_shortcode('[jszip post_id="'.$post->ID.'" post_name="'.$post->post_name.'"]');
function jszip_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'post_id' => $post->ID,
        'post_name' => $post->post_name,
        'text' => 'Descargar imágenes en zip',
        'style' => 'background-color: #337ab7;text-decoration: none;color: #fff;border-radius: 4px;padding: 10px 15px;',
        'loader' => '<img src="'.plugins_url( '/loader.gif', __FILE__ ).'">',
    ), $atts );

    $args = array(
		'order' => 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $a['post_id'],
		'post_type' => 'attachment',
	);
	$attachments = get_children( $args );

    if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$thumb = wp_get_attachment_image_src( $attachment->ID, 'full' );
			$url_thumb = $thumb['0'];
		}
	}

    echo '<script type="text/javascript">
				var zip = new JSZip();
				var images = [';
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$thumb = wp_get_attachment_image_src( $attachment->ID, 'full' );
			$url_thumb = $thumb['0'];
			echo '"'.$url_thumb.'",';
		}
	}

	echo		'],
				    index = 0;
				function loadAsArrayBuffer(url, callback) {
				  var xhr = new XMLHttpRequest();
				  xhr.open("GET", url);
				  xhr.responseType = "arraybuffer";
				  xhr.onerror = function() {/* handle errors*/};
				  xhr.onload = function() {
				    if (xhr.status === 200) {callback(xhr.response, url)}
				    else {/* handle errors*/}
				  };
				  xhr.send();
				}
				(function load() {
				  if (index < images.length) {
				    loadAsArrayBuffer(images[index++], function(buffer, url) {
				      var filename = getFilename(url);
				      zip.file(filename, buffer);
				      load();
				    })
				  }
				  else {
				    zip.generateAsync({type:"blob"}).then(function(content) {
				      lnk.href = (URL || webkitURL).createObjectURL(content);
				      lnk.innerHTML = "'.$a['text'].'";
				      lnk.download = "'.$a['post_name'].'.zip";
				    });
				  }
				})();
				function getFilename(url) {
				  return url.substr(url.lastIndexOf("/") + 1)
				}
			</script>
			<a id="lnk" style="'.$a['style'].'">'.$a['loader'].'</a>';
}
add_shortcode( 'jszip', 'jszip_shortcode' );


?>
