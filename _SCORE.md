# Scoring Fluid checklists from external notes

Fluid checklists can be scored by supplying situation notes from an external
knowledge system, assessing each note against the descriptions of a Fluid
checklist, and then recording the result through the checklist interface or
its equivalent database operations.

For each note, assess every checklist item using this rule:

- Mark **complete** when the note clearly demonstrates the behavior described
  by the item.
- Leave both **complete** and **ignored** unticked when the note clearly
  demonstrates the opposite behavior. This records a relevant but
  unsuccessful observation.
- Mark **ignored** when the item is not relevant to the note or the evidence
  is ambiguous. Do not infer failure merely from missing evidence.

Process notes one at a time. Before each note, clear the temporary checkbox
state from the previous note, apply the complete and ignored selections, and
leave relevant opposite-behavior items unticked. Then use the checklist's
**Clear all** action. For scored checklists, this increments `assessed` for
each non-ignored item and increments `score` for each completed item before
clearing the temporary selections.

Do not alter the source notes while scoring. Preserve the distinction between
an ignored observation and an incomplete observation: ignored items do not
contribute to the denominator, while incomplete items do. If a source note
refers to multiple people, apply the assessment only to the person or subject
specified by the task.

When using direct database access, update the instance-specific checklist rows
and reproduce the application order: set temporary flags, assess all
non-ignored rows, score completed rows, then clear both temporary flags. Do not
reset accumulated `assessed` or `score` values unless explicitly asked to
discard prior observations.
