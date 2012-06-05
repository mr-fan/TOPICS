//Starting Javascript
selectDropdownOption (document.modify.sort_topics, 0);
document.modify.topics_per_page.value = '1';
document.modify.picture_dir.value = '/media/topics-pictures';
document.modify.header.value = '<!-- Header -->\n';
document.modify.topics_loop.value = '';
document.modify.footer.value = '';
document.modify.topic_header.value = '<h3 style="margin-bottom:20px">Alle Artikel in "Einzelansicht":</h3>{FULL_TOPICS_LIST} <hr style="clear:both" />\n<h1>[TITLE]</h1>\n{PICTURE}<div style="font-weight:bold">[TOPIC_SHORT]</div>\n';
document.modify.topic_footer.value = '{SEE_ALSO}\n\n{SEE_PREVNEXT}\n<hr/>\n<a href="[BACK]">BACK</a>';
document.modify.topic_block2.value = '<!--see help how to use a second block -->';
document.modify.see_also_link_title.value = '<h4>Siehe auch:</h4>';
document.modify.next_link_title.value = '<h4>Vorige/nächste Themen:</h4>';
document.modify.previous_link_title.value = '';
document.modify.pnsa_string.value = '<p style="width:40%;float:left;"><a href="[LINK]">[TITLE]</a><span class="pnsa_link" style="width:100%;">\n[SHORT_DESCRIPTION]</span></p>';
document.modify.sa_string.value = '<p style="width:49%;"><a href="[LINK]">[TITLE]</a><span class="pnsa_link" style="width:100%;">\n[SHORT_DESCRIPTION]</span></p>';
selectRadioButtons (document.modify.sort_comments, 1);
selectRadioButtons (document.modify.use_captcha, 1);
selectDropdownOption (document.modify.commenting, 2);
selectDropdownOption (document.modify.default_link, 3);
document.modify.comments_header.value = '<h2>Kommentare</h2>';
document.modify.comments_loop.value = '<blockquote class="topics-commentbox">\n<p class="comment_date">[DATE]</p>\n<p class="comment_name">{NAME}</p>\n<p class="comment_text">[COMMENT]</p>\n</blockquote>';
document.modify.comments_footer.value = '';


// To save as a preset, change this line with your description:
document.getElementById('presetsdescription').innerHTML = 'Single Topics, see: <a href="http://websitebaker.at/wb/module/topics-playground/topics-einzeln.html">here</a>';

alert("Done");