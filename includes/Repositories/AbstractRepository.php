<?php
/**
 * Abstract base repository providing generic CRUD helpers.
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
	 * Find a single row by primary key.
	 *
	 * @return array<string,mixed>|null
	 */
	public function find( int $id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d LIMIT 1", $id ),
			ARRAY_A
		);
		return $row ?: null;
	}

	/**
	 * Find all rows matching simple key=value conditions.
	 *
	 * @param  array<string,mixed> $conditions
	 * @return array<int,array<string,mixed>>
	 */
	public function findBy( array $conditions = [], string $orderBy = 'id', string $direction = 'ASC', int $limit = 0 ): array {
		$sql    = "SELECT * FROM {$this->table}";
		$values = [];

		if ( ! empty( $conditions ) ) {
			$clauses = [];
			foreach ( $conditions as $col => $val ) {
				$clauses[] = "`{$col}` = %s";
				$values[]  = $val;
			}
			$sql .= ' WHERE ' . implode( ' AND ', $clauses );
		}

		$sql .= " ORDER BY `{$orderBy}` {$direction}";

		if ( $limit > 0 ) {
			$sql    .= ' LIMIT %d';
			$values[] = $limit;
		}

		if ( ! empty( $values ) ) {
			$sql = $this->wpdb->prepare( $sql, ...$values ); // phpcs:ignore
		}

		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/**
	 * Insert a row. Returns the new ID or throws.
	 *
	 * @param array<string,mixed> $data
	 */
	public function insert( array $data ): int {
		$result = $this->wpdb->insert( $this->table, $data );

		if ( false === $result ) {
			throw new \RuntimeException( "DB insert failed: {$this->wpdb->last_error}" );
		}

		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Update rows matching $where with $data. Returns rows affected.
	 *
	 * @param array<string,mixed> $data
	 * @param array<string,mixed> $where
	 */
	public function update( array $data, array $where ): int {
		$result = $this->wpdb->update( $this->table, $data, $where );
		return (int) $result;
	}

	/**
	 * Delete rows matching $where. Returns rows affected.
	 *
	 * @param array<string,mixed> $where
	 */
	public function delete( array $where ): int {
		$result = $this->wpdb->delete( $this->table, $where );
		return (int) $result;
	}

	/** Count all rows (optionally filtered). */
	public function count( array $conditions = [] ): int {
		$sql    = "SELECT COUNT(*) FROM {$this->table}";
		$values = [];

		if ( ! empty( $conditions ) ) {
			$clauses = [];
			foreach ( $conditions as $col => $val ) {
				$clauses[] = "`{$col}` = %s";
				$values[]  = $val;
			}
			$sql .= ' WHERE ' . implode( ' AND ', $clauses );
			$sql  = $this->wpdb->prepare( $sql, ...$values ); // phpcs:ignore
		}

		return (int) $this->wpdb->get_var( $sql );
	}
}
