<!-- <?php print_r($contacts); ?> -->
<div id="content">

<table>
<?php foreach ($contacts as $k => $v): ?>
    <tr><th colspan="1">Contacts of <?php echo $k; ?></th></tr>

    <tr><td>
    <?php foreach ($v as $contact): ?>
            <a id="fb_character" href="<?php echo site_url('/fancybox/character/'.$contact['contactID']); ?>" title="<?php echo $contact['contactName']; ?>">
                <?php echo get_character_portrait($contact['contactID'], 64, 'entry'); ?>
            </a>
    <?php endforeach;?>
    </td></tr>
<?php endforeach; ?>

</table>
</div>
