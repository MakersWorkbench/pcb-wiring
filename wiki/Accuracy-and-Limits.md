# Accuracy and Limits


The tools use the IPC-2221 formulas and tables. [SOURCES.md](https://github.com/MakersWorkbench/pcb-wiring/blob/main/SOURCES.md) lists where each one comes from.

- **IPC-2221 versus IPC-2152.** IPC-2221 is simple and conservative for many boards. IPC-2152 is newer and based on measurements, and results depend on the board stack-up, copper planes, and airflow. Treat this tool as a safe starting point.
- **Via current is an estimate.** Published data on vias is limited. Use several vias in parallel and keep a margin.
- **Spacing is general guidance.** Mains and safety-critical designs must follow the applicable safety standards (such as IEC 62368-1 and IEC 60664).
- **Wire** results are resistance and voltage drop, not safe current. Check the wire's datasheet for ampacity.

Always confirm with the standards, your board maker's design rules, and a test.
