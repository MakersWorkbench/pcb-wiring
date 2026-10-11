# Trace Width

A copper trace on a circuit board is a tiny wire. Too narrow for the current and it heats up, which can lift the copper, burn the board, or cause failures. This tool finds the narrowest trace that stays within a temperature rise you choose.

## The theory

The standard rule is the **IPC-2221** formula:

**I = k × ΔT^0.44 × A^0.725**

where I is the current in amps, ΔT is the temperature rise above ambient in °C, and A is the cross-section of the trace in square mils (a mil is a thousandth of an inch, 0.0254 mm). The constant **k** is **0.048 for outer layers** and **0.024 for inner layers**, because inner traces are buried and cannot shed heat into the air.

The cross-section is width × thickness. Copper thickness is given as a weight: **1 oz copper is about 35 µm (1.378 mil)**, 2 oz is about 70 µm. Solving for width: **width = A ÷ thickness**.

IPC-2221 is the long-standing, simple rule. The newer **IPC-2152** is based on measurements and shows real boards often run cooler, depending on the board and nearby copper planes. IPC-2221 is a safe starting point. Its data covers currents up to about 35 A.

## Step by step

1. Open the **Trace Width** tab.
2. In **Current (A)**, type the steady current the trace carries, for example `2`.
3. In **Copper weight**, choose your board's copper (1 oz is the standard).
4. In **Allowed temperature rise (°C)**, type how much hotter than the board you accept, for example `10`.
5. In **Layer**, choose **External** or **Internal**.
6. Read **Minimum trace width** in mm and mil.

## Worked example

2 A on a 1 oz outer layer with a 10 °C rise needs a trace at least **0.78 mm (30.8 mil)** wide. The same current on an inner layer needs about **2.03 mm (80 mil)**, which is 2.6 times wider.

## Things to watch

- For long traces, voltage drop can matter more than heating. See [Trace Resistance](Trace-Resistance).
- A wide copper pour, extra layers, or thicker copper are all better ways to carry heavy current.
- Short narrow necks (like a pad connection) are fine when they are short, but the main run should meet the width.

![Trace Width tool](images/trace.png)
