<?php
if (!defined('OT'))
	die();

$expiry_time = calc_expiry();

?>
<style type="text/css">
button.small { display: inline; }
#password { display: inline-block;}
</style>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
	function el(id) { return document.getElementById(id); }
	function onSubmit(token) {
		el("message-form").submit();
	}

	function wordIsh(word_length, num_words) {
		// Copyright (c) kodespace.com. All rights reserved
		var MAX_RAND = 255;
		// the 'popularity' is made up & has no basis in science ;)
		var vowelish = {
			list: [
				{ popularity: 10, list: 'aeiou'.split('') }, // 10:3 chance of hitting a vowel
				{ popularity: 2, list: ['oo', 'ee', 'ea']},
				{ popularity: 1, list: ['ae','ai','ao','au', 'eo', 'ia','io','iu', 'ou','oe','ua', 'ue', 'ui', 'uo'] }
			]
		};
		var consonantish = { list:
			[
				{ popularity: 20, list: 'nrst'.split('') }, // most popular letter, 120-ish% more popular than next category
				{ popularity: 8, list: 'bcdlm'.split('') },
				{ popularity: 5, list: 'fghp'.split('') },
				{ popularity: 2, list: 'jkv'.split('') },
				{ popularity: 1, list: 'qwxyz'.split('') },
				{ popularity: 5, list: ['th', 'sh', 'ch', 'mm', 'nn', 'ss', 'tt'] }
			]
		}
		function prepList(listish) {
			var totalPopularity = listish.list.reduce(function(sum, el) { return sum + el.popularity; }, 0)
			// calculate position in the range 0..255 where each list fits in.
			//	(each letter in the list is a subset of the smaller range, eg, 245.2..248.7)
			var prevCeiling = 0; 
			listish.list.forEach(function(el) {
				el.range = el.popularity*MAX_RAND/totalPopularity;
				el.floor = prevCeiling; 
				el.ceil  = prevCeiling + el.range; // this is an accumulative upper total of the ranges
				prevCeiling = el.ceil;
			})
			listish.get = function(rand) {
				var el = this.list.find(function(el) { return rand>=el.floor && rand<el.ceil; });
				if (!el) el = this.list[this.list.length-1];
				var inner_list = el.list;
				var ofs = rand - el.floor;
				var idx = Math.floor(ofs*inner_list.length/el.range);
				if (idx>=inner_list.length) idx = inner_list.length-1; // range check
				return inner_list[idx];
			}
		}
		prepList(vowelish);
		prepList(consonantish);

		var letterPool = [
			vowelish,
			consonantish
		];
		var words = [];
		for (;words.length<num_words;){
			var i = 0;
			var ar = new Uint8Array(1+word_length);
			window.crypto.getRandomValues(ar);
			var pool = ar[i++]>60 ? 1 : 0; // randomly choose vowel or consonant. Prefer consonants though.
			var word = '';
			while (i<ar.length) {
				word += letterPool[pool].get(ar[i++]);
				pool = 1-pool; // change vowel/consonant
			}
			words.push(word);
		}
		return words;
	}
	function onGenerate() {
		//el('password').value = Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2); // from https://stackoverflow.com/a/29770068/125525
		el('password').value = wordIsh(7, 5).join('-');
	}
 </script>

<form id='message-form' method="POST">

	<label for='message'>Enter your one time message:</label>
	<textarea name='message'></textarea>

	<label for='email'>Optional. E-mail address of the recipient:</label>
	<input name='email' type='email'/><br>

	<label for='password' class='small'>Optional. Enter a long password<sup>*</sup>:</label>
	<input name='password' id="password" type='text' class='small'/><button class="small" id="generate" onclick="onGenerate();return false;">Generate</button><br>
	
	<label class="small">This message will expire on: <?php echo $expiry_time;?>.</label>
	<button class="g-recaptcha" data-sitekey="<?php echo OT_RECAPTCHA; ?>" data-callback="onSubmit">Submit</button>
	<p class='small italics'><sup>*</sup>You will need to SMS your password to the recipient manually.</p>
</form>
