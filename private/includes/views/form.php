<?php
if (!defined('OT'))
	die();

$expiry_time = calc_expiry();

?>
<style type="text/css">
#password { display: inline-block;}
</style>
<?php if (defined('OT_RECAPTCHA')) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>
<script>
	function el(id) { return document.getElementById(id); }
	function onSubmit(token) {
		el("message-form").submit();
	}

	// because a dictionary containing correct-horse-battery-staple isn't available, in client-side js we're limited to making up stuff. 
	// Enter wordIsh
	function wordIsh(word_length, num_words) {
		// Copyright (c) kodespace.com. All rights reserved
		// A monkeys program might be better ;) ie a tree of letter choices based on real texts. You'd get closer to lorem ipsum
		var MAX_RAND = 255;
		// based on data from https://en.wikipedia.org/wiki/Letter_frequency
		var vowelish = {
			list: [
				{ popularity: 11.688, list: ['a'] },
				{ popularity: 2.799, list: ['e'] },
				{ popularity: 7.294, list: ['i'] },
				{ popularity: 7.631, list: ['o'] },
				{ popularity: 1.183, list: ['u'] },
				{ popularity: 3, list: ['oo', 'ee', 'ea']}, // made up value = 3*1%
				{ popularity: 10, list: ['ae','ai','ao','au', 'eo', 'ia','io','iu', 'ou','oe','ua', 'ue', 'ui', 'uo'] } // made up value = 14*0.7%
			]
		};
		var consonantish = { list:
			[
				{ popularity: 4.434, list: ['b'] },
				{ popularity: 5.238, list: ['c'] },
				{ popularity: 3.174, list: ['d'] },
				{ popularity: 4.027, list: ['f'] },
				{ popularity: 1.642, list: ['g'] },
				{ popularity: 4.200, list: ['h'] },
				{ popularity: 0.511, list: ['j'] },
				{ popularity: 0.456, list: ['k'] },
				{ popularity: 2.415, list: ['l'] },
				{ popularity: 3.826, list: ['m'] },
				{ popularity: 2.284, list: ['n'] },
				{ popularity: 4.319, list: ['p'] },
				{ popularity: 0.222, list: ['q'] },
				{ popularity: 2.826, list: ['r'] },
				{ popularity: 6.686, list: ['s'] },
				{ popularity:15.978, list: ['t'] },
				{ popularity: 0.824, list: ['v'] },
				{ popularity: 5.497, list: ['w'] },
				{ popularity: 0.045, list: ['x'] },
				{ popularity: 0.763, list: ['y'] },
				{ popularity: 0.045, list: ['z'] },
				{ popularity: 7    , list: ['th', 'sh', 'ch', 'mm', 'nn', 'ss', 'tt'] } // made up value = 7*1%
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

	<label for='password' class='small'>Optional. Password<sup>*</sup>:</label>
	<input name='password' id="password" type='text' class='small'/><button class="small" id="generate" onclick="onGenerate();return false;">Generate</button><br>
	
	<label class="small">This message will expire on: <?php echo $expiry_time;?>.</label>
	<button class="g-recaptcha" data-sitekey="<?php echo OT_RECAPTCHA; ?>" data-callback="onSubmit">Submit</button>
	<p class='small italics'><sup>*</sup>You will need to SMS your password to the recipient manually. The longer, the better.</p>
	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
</form>
