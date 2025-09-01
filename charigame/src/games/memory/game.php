<div id="container">
	<div id="gamesection"
		 class="mb-16">
		<div class="flex justify-center">
<!--			--><?php //$memory_settings = carbon_get_post_meta( get_the_ID(), 'memory_settings' ); ?>
			<div class="memory_game_grid game mt-20"
				 data-dimension="2"
				 data-background="<?php echo esc_attr( carbon_get_post_meta( get_the_ID(), 'login_form_logo' ) ); ?>">
				<div class="board-container aspect-square">
					<div class="board aspect-square"></div>
				</div>
				<div class="controls">
					<div class="stats flex justify-between w-full">
						<div class="moves">0 Karten umgedreht</div>
						<div class="timer">Zeit: 0 Sekunden</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="picker-container"
	 class="lg:my-0 bg-gray-100 max-sm:w-[80%] max-sm:mx-auto">
	<div class="lg:w-1/3 lg:mx-auto">
		<div id="picker"
			 class="picker">
		</div>
	</div>
	<div class="flex justify-center">
		<button id="btn-submit-score"
				class="hidden flex-row items-center justify-between mt-8 px-4 xs:px-8 py-3 xs:py-[1.125rem] w-max bg-secondary hover:bg-white rounded-lg text-white font-medium text-lg xs:text-xl hover:text-secondary hover:ring-2
                hover:ring-secondary cursor-pointer max-sm:mt-40"
				title="Spende verteilen">
			Spende verteilen
		</button>
	</div>
</div>
<?php
//$spendenverteilung_gruppe = carbon_get_post_meta( get_the_ID(), 'donation_distribution_group' );
//if ( $spendenverteilung_gruppe != null ) {
//	$highscore       = $spendenverteilung_gruppe['highscore'];
//	$gewinnkategorie = $spendenverteilung_gruppe['gewinnkategorie'];
//	if ( !$highscore ) {
//		$spendenbetrag = $gewinnkategorie[0]['spendenbetrag'];
//	} else {
//		$spendenbetrag = $gewinnkategorie[ count( $gewinnkategorie) - 1 ]['spendenbetrag'];
//	}
//} else {
//	$spendenbetrag = 0;
//}
?>
<div id="btn-play-again-end-container"
	 class="flex justify-center items-center flex-col hidden">
	<div class="flex justify-center"><p class="text-center">Sehr gut. Versuchen Sie den maximalen Spendenbetrag
<!--															von --><?php //echo number_format( $spendenbetrag, 2 ); ?><!--€-->
															zu erreichen!</p></div>
	<button id="btn-play-again-end"
			class="btn-play-again-end flex justify-center w-[305px] px-4 xs:px-8 py-3 xs:py-[1.125rem] my-4 text-secondary bg-white hover:bg-secondary hover:text-white rounded-lg font-medium text-lg xs:text-xl ring-2
                ring-secondary cursor-pointer"> Noch mal versuchen
	</button>
	<p class=" text-sm text-center mb-8">Beachten Sie, dass Ihr erspieltes Ergebnis überschrieben wird.
		<br>Wenn Sie zufrieden sind mit
										 Ihrem Ergebnis, können Sie jederzeit <a class="text-[#28333E]"
																				 href="<?php echo home_url(); ?>">unsere Website besuchen</a>
	</p>
</div>


	<div id="modal-game-end"
		 class="bg-gradient-to-t from-secondary to-primary fixed z-50 inset-0 hidden rounded-b-3xl overflow-y-hidden">
		<div
			 class="intro-seperator -mt-1 w-full max-h-8 seperator-white"
		>
<!--		--><?php //echo $torn_bottom; ?>
	</div>
		<div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
			<div
				class="bg-white border-t-8 border-t-primary inline-block align-bottom rounded-b-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 container my-auto mx-auto px-6 max-w-5xl"
				role="dialog"
				aria-modal="true"
				aria-labelledby="modal-headline">
				<div class="bg-dark px-4 pt-5 sm:p-6 sm:pb-4">
					<div class="">
						<div class="pt-6 text-center sm:mt-0">
							<h3 class="text-3xl sm:text-6xl"
								id="popup-game-finished">
								Vielen Dank für Ihre Teilnahme!
							</h3>
							<div class="mt-6 sm:mt-12">
								<p class="text-xl sm:text-3xl">
									Sie haben insgesamt <span id="game-points"
															  class="text-secondary">x</span> <span id="game-objectives">Karten umgedreht</span> und
									<span id="game-time"
										  class="text-secondary">x</span>
									<span id="game-time-unit">O</span> benötigt.
								</p>
								<p id="scored" class="text-xl sm:text-3xl">
									Der Spendentopf erhöht sich damit um <span id="personal-bonus"
																			   class="text-secondary">x</span>€.
								</p>
								<p id="not-scored" class="text-xl sm:text-3xl pt-4">
									Der Spendentopf konnte diesmal nicht erhöht werden.<br>
									Geben Sie nicht auf – versuchen Sie es gleich nochmal!
								</p>
							</div>
						</div>
					</div>
				</div>
				<div class="px-4 sm:px-6 pt-3 lg:pt-12 pb-10 lg:pb-14 flex flex-col justify-center items-center">
					<button id="show-donation-triangle"
							class="btn-show-donation flex justify-center w-[305px] mt-8 px-4 xs:px-8 py-3 xs:py-[1.125rem] bg-secondary hover:bg-white rounded-lg text-white font-medium text-lg xs:text-xl hover:text-secondary hover:ring-2
                hover:ring-secondary cursor-pointer">Spendentopf erhöhen & verteilen
					</button>
					<button id="btn-play-again"
							class="btn-play-again flex justify-center w-[305px] mt-8 px-4 xs:px-8 py-3 xs:py-[1.125rem] bg-teritary hover:bg-white rounded-lg text-white font-medium text-lg xs:text-xl hover:text-teritary hover:ring-2
                hover:ring-teritary cursor-pointer"> Noch mal versuchen
					</button>

				</div>

			</div>
		</div>
	</div>
