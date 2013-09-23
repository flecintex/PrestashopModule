<!-- Block packlink -->
<style>
    .packLinkTrackCaja input[type="text"] { border: 1px solid #CCCCCC; margin: 3px 0 0; outline: medium none; padding: 3px; width: 96.5%; }
    .packLinkTrackCaja input[type="submit"] { float: right; margin-top: 5px; }
    .packLinkTrackTexto { padding:10px 0 5px 0; }
</style>
<div id="packlink_block_left" class="block">
	<h4>{l s='Tracking PackLink' mod='packlink'}</h4>
	<div class="packLinkTrack">
            <div class="packLinkTrackTexto seguir">
                Número de envío PackLink:<br>
                (ejemplo ES123456789)
            </div>
            <div class="packLinkTrackCaja seguir">
                <form action="{$base_dir}modules/packlink/tracking.php" method="POST" id="form_num">
                    <input type="text" name="num" id="num" class="inputTrack" />
                    <input type="submit" name="sendit" value="Consultar" class="exclusive standard-checkout" />
                </form>
            </div>
        </div>
</div>
<!-- /Block packlink -->
