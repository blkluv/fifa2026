<?php
/**
 * Abstract Repository - base class for all repositories.
 *
 * @package WC26Predictor\Repositories
 */

declare(strict_types=1);

namespace WC26Predictor\Repositories;

abstract class AbstractRepository {

	protected \wpdb $wpdb;
	protected string $table;

	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . $this->getTableName();
	}

	abstract protected function getTableName(): string;

	/**
	 * Find a record by ID.
	 */
	public function find( int $id ): ?array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE id = %d LIMIT 1",
			$id
		);
		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	/**
	 * Find records by conditions.
	 *
	 * @param array<string,mixed> $where
	 * @param string              $order_by
	 * @param string              $order_dir
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function findBy( array $where, string $order_by = 'id', string $order_dir = 'ASC' ): array {
		$conditions = [];
		foreach ( $where as $key => $value ) {
			$conditions[] = $this->wpdb->prepare( "`{$key}` = %s", $value );
		}
		$where_sql = ! empty( $conditions ) ? 'WHERE ' . implode( ' AND ', $conditions ) : '';
		$sql       = "SELECT * FROM {$this->table} {$where_sql} ORDER BY `{$order_by}` {$order_dir}";
		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/**
	 * Find all records.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function findAll(): array {
		$sql = "SELECT * FROM {$this->table}";
		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/**
	 * Insert a new record.
	 *
	 * @param array<string,mixed> $data
	 *
	 * @return int Insert ID.
	 */
	public function insert( array $data ): int {
		$this->wpdb->insert( $this->table, $data );
		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Update records.
	 *
	 * @param array<string,mixed> $data
	 * @param array<string,mixed> $where
	 *
	 * @return int Number of rows affected.
	 */
	public function update( array $data, array $where ): int {
		return (int) $this->wpdb->update( $this->table, $data, $where );
	}

	/**
	 * Delete records.
	 *
	 * @param array<string,mixed> $where
	 *
	 * @return int Number of rows affected.
	 */
	public function delete( array $where ): int {
		return (int) $this->wpdb->delete( $this->table, $where );
	}

	/**
	 * Get table name with prefix.
	 */
	public function getTable(): string {
		return $this->table;
	}
}
