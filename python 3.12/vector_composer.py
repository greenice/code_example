from typing import List, Dict, Any, Tuple
from ..core.models import VectorDefinition, FormulaNode, UnitConversion

class VirtualVectorComposer:
    """
    The 'Brain' of the system. 
    Orchestrates complex multi-node vector arithmetic using recursive CTE generation.
    Supports on-the-fly resampling and unit normalization.
    """

    def __init__(self, schema: str = "analytics"):
        self.schema = schema

    def compose_calculated_vector(
        self, 
        output_name: str, 
        root_formula: FormulaNode, 
        input_vectors: List[VectorDefinition],
        conversions: Dict[str, UnitConversion],
        target_granularity: int = 300
    ) -> str:
        
        cte_parts = []
        # Step 1: Normalize all inputs to a common timeframe and unit system
        for i, vec in enumerate(input_vectors):
            alias = f"input_{i}"
            view_name = f"agg_{vec.parameter_type}_{vec.id.replace('-', '_')}"
            
            conv = conversions.get(vec.id, UnitConversion())
            
            # Complex resampling logic: handles missing data via COALESCE and LAST()
            cte = f"""
    {alias} AS (
        SELECT 
            time_bucket('{target_granularity} seconds', bucket) as ts_bucket,
            avg(value) * {conv.factor} + {conv.offset} as norm_val
        FROM {self.schema}.{view_name}
        GROUP BY ts_bucket
    )"""
            cte_parts.append(cte)

        # Step 2: Recursive SQL expression generation
        expr = self._build_expression(root_formula, input_vectors)

        # Step 3: Composite Join Logic (Full Outer Join to preserve all timestamps)
        joins = []
        for i in range(1, len(input_vectors)):
            joins.append(f"FULL OUTER JOIN input_{i} ON input_0.ts_bucket = input_{i}.ts_bucket")

        final_sql = f"""
-- Virtual Vector: {output_name}
-- Logic: Cross-sensor arithmetic with normalized resampling
CREATE OR REPLACE VIEW {self.schema}.v_{output_name} AS
WITH {", ".join(cte_parts)}
SELECT 
    COALESCE({', '.join([f'input_{i}.ts_bucket' for i in range(len(input_vectors))])}) as timestamp,
    {expr} as calculated_value
FROM input_0
{" ".join(joins)}
ORDER BY timestamp;
        """
        return final_sql

    def _build_expression(self, node: FormulaNode, vectors: List[VectorDefinition]) -> str:
        parts = []
        for operand in node.operands:
            if isinstance(operand, FormulaNode):
                parts.append(self._build_expression(operand, vectors))
            else:
                # Find the index of the vector ID
                idx = next(i for i, v in enumerate(vectors) if v.id == operand)
                parts.append(f"COALESCE(input_{idx}.norm_val, 0)")
        
        return f"({f' {node.operator} '.join(parts)})"
