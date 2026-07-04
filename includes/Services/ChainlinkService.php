require_once WC26_PLUGIN_DIR . 'includes/Repositories/Repositories.php';
<?php
/**
 * Chainlink CRE Service - manages DON reports and settlement
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\ChainlinkReportRepository;

class ChainlinkService {

	private ChainlinkReportRepository $repository;

	public function __construct() {
		$this->repository = new ChainlinkReportRepository();
	}

	/**
	 * Create a new Chainlink CRE report
	 */
	public function createReport( int $marketId, string $donId, array $reportData, ?string $signature = null ): int {
		return $this->repository->create( $marketId, $donId, $reportData, $signature );
	}

	/**
	 * Update report status
	 */
	public function updateStatus( int $reportId, string $status, ?string $txHash = null, ?string $error = null ): void {
		$this->repository->updateStatus( $reportId, $status, $txHash, $error );
	}

	/**
	 * Get report by ID
	 */
	public function getReport( int $reportId ): ?array {
		return $this->repository->find( $reportId );
	}

	/**
	 * Get pending reports
	 */
	public function getPending(): array {
		return $this->repository->findPending();
	}

	/**
	 * Get reports for a market
	 */
	public function getReportsForMarket( int $marketId ): array {
		return $this->repository->findByMarket( $marketId );
	}

	/**
	 * Event handler: market settled - create Chainlink report
	 */
	public function onMarketSettled( int $marketId, string $outcome, float $confidence ): void {
		$donId = get_option( 'wc26_chainlink_don_id', '' );
		if ( ! $donId ) {
			return;
		}

		$this->createReport( $marketId, $donId, [
			'market_id'   => $marketId,
			'outcome'     => $outcome,
			'confidence'  => $confidence,
			'timestamp'   => current_time( 'mysql' ),
			'source'      => 'wordpress',
		] );
	}
}
