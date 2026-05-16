<button onclick="toggleTable('assumptions')" class="cont">Mx Assumptions</button>
<?php if ($qLimit == 'e') echo '<button onclick="toggleTable(\'careerAssumptions\')" class="cont">Career Assumptions</button>'; ?>
<button onclick="toggleTable('pageSummary')" class="cont">Page stats</button>

<table id="assumptions" style="display: none;">
    <tr>
        <td class="mx">Quality <img src="media/contract.gif" height=80px>Concern with degree of excellence to which a service or function is performed, and degree to which expectations are set and met</td>
    </tr><tr>
        <td class="mx">Human <img src="media/is it to be desired.jpg" height=80px>Concern with how myself and others are treated, rewarded, or individually affected by actions or existing situations</td>
    </tr><tr>
        <td class="mx">Resource <img src="media/sunrise.png" height=80px>Concern with accumulation of those currencies which serve the purposes of an entity, and with optimising the frameworks through which such resources are generated</td>
    </tr><tr>
        <td class="mx">Innovation <img src="media/ireland.gif" height=60px>Seeking to imagine, explore, trial and implement new or alternative merit-based patterns, and to foster curiosity</td>
    </tr><tr>
        <td class="mx">Environment <img src="media/cakes.jpg" height=80px> Attention to environments, whether natural, social, cultural, or political, and with responses to action from entities within the environments</td>
    </tr><tr>
        <td class="mx">Perception <img src="media/old-ties.jpg" height=80px> Interest in constructing representations and how representations are identified and perceived by different parties, and with rectifying negative interpretations</td>
    </tr><tr>
        <td class="mx">utility by attribute = sum of (probability x value)</td>
    </tr><tr>
        <td class="mx">summary scores show inverse multiplier of weighted preference for attribute, with equilibrium when results between summaries similar</td>
    </tr><tr>
        <td class="mx">value = reward - loss</td>
    </tr><tr>
        <td class="mx">cubic scores from -4 to +4 for loss to gain (1 insignificant, 2 weak, 3 sound, 4 strong)</td>
    </tr><tr>
        <td class="mx">ordinal probabilities from 0 to 9 for likelihood (0%, 10%, 20% ... 90%)</td>
    </tr><tr>
        <td class="mx">ordinal weighting from 0 to 9 for preference</td>
    </tr><tr>
        <td class="mx">ordinal integers from -n to +n for time</td>
    </tr><tr>
        <td class="mx">scores based on conversation, description, research and experience</td>
    </tr><tr>
        <td class="mx">scores may be revised in light of new experience or information</td>
    </tr><tr>
        <td class="mx">scores vary over life cycle of projects and of my life</td>
    </tr><tr>
        <td class="mx">the way needs are satisfied shifts over changing roles, and short and long term</td>
    </tr><tr>
        <td class="mx">hours: 3 = a morning, 3 = an afternoon, 3/2 = an evening, <?php echo $unqhoursyear; ?> = a year, add 750 = offset a year</td>
    </tr><tr>
        <td class="mx">reality check that things take twice as long as I estimate. Offset 750 hours/year for some projects due to brain downtime.</td>
    </tr><tr>
        <td class="mx">8760 hours a year, minus offset = 8000, <?php echo $unqhoursyearbrainless; ?> brainless</td>
    </tr><tr>
        <td class="mx">items can be deferred even if hours have been spent because hours simply indicate capacity relative to the coming 12 months</td>
      </tr><tr>
        <td class="mx">season scores 6 to 9 for fewer tourists and better temperature/rain, 6 = poor weather, 7 = peak tourist but good weather, 8 = do-able options despite weather, 9 = off-peak but fair weather</td>
    </tr><tr>
        <td class="mx">camera on screen shows awkward or false expression when lying about valuation</td>
    </tr>
</table>

<div id="careerAssumptions" style="display: none;"><br><br>
<!-- <div id="careerAssumptions"><br><br> -->

<table>
  <tr><td class="mx">Career Sum Score = Personal Fit * (Impact + Career Capital + Conditions) ~ consider alongside Certainty, Effort and Years</td></tr>
  <tr><td class="mx noed"></td></tr>
  <tr><td class="mx">Impact score = (Scale + Neglectedness + Solvability)</td></tr>
  <tr><td class="mx">Scale score = max (Extinction reduction, Global economy, Poorest income, Healthy years) ~ informed by (Beneficiaries, Benefits)</td></tr>
  <tr><td class="mx">Neglectedness score = min (Annual spending, Staff numbers, Supporter numbers) ~ informed by (Beneficiaries, Benefits)</td></tr>
  <tr><td class="mx">Solvability score ~ informed by (Costs, Risks, Solution, Professional Practices, Standard of Outputs, Delegation (?), Recovery (?))</td></tr>
  <tr><td class="mx noed"></td></tr>
  <tr><td class="mx">Personal Fit score = (Repeat + Incomplete + Align with Abilities + Decile of Abilities) * weights ~ informed by (Age min, Behaviour, Standard, Conditions, Work Role, Precursor)</td></tr>
  <tr><td class="mx noed"></td></tr>
  <tr><td class="mx">Career Capital score = (Build skills + Connections + Credentials) * weights</td></tr>
  <tr><td class="mx noed"></td></tr>
  <tr><td class="mx">Conditions Sum score = (Reasonable Demand + Harmony with Values + Personal life + Conditions Needs score) * weights ~ informed by (Collaborators, Start Date)</td></tr>
  <tr><td class="mx">Conditions Needs score = (Manageable hours + Conditions Locality score + Conditions Income score)</td></tr>
  <tr><td class="mx">Conditions Locality score = (Desirable Location + Allergy Tolerable + Fast Transport) ~ informed by Travel/Year</td></tr>
  <tr><td class="mx">Conditions Income score = (Fair Pay + Job Security) ~ informed by (Cost-earn/Year, Cost-earn Notes)</td></tr>
  <tr><td class="mx noed"></td></tr>
  <tr><td class="mx">Certainty = ?</td></tr>
  <tr><td class="mx">Effort/Year = (Effort/Day * Days/Year) + Hours Research</td></tr>
  <tr><td class="mx">Travel/Year = (Travel/Day * Days/Year)</td></tr>
  <tr><td class="mx noed"></td></tr>
</table>

<!-- paste from FR > estimates by 80k method v0.2 > mx format -->
<table>
<tr>	<td class="mxah" colspan="13" style="text-align: left;"	title="Ultimately we want to know the expected 'good done' per unit of resources invested in a problem, ie one year of effort or per dollar donation">	Problem ~ Impact table	</td>
</tr><tr>	<td class="mxah"	title="If we solved this problem, by how much would the world become a better place? Solving this problem would be equivalent to:">	Scale	</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">	</td><td class="mxah"	title="How many people, or dollars, are currently being dedicated to solving the problem?">	Neglectedness	</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">	</td><td class="mxah"	title="If we doubled direct effort on this problem, what fraction of the remaining problem would we expect to solve?">	Solvability	</td><td class="mxa">			</td>
</tr><tr>	<td class="mxah"	title="Illustrative examples on each score">	Score	</td><td class="mxah"	title="A reduction in the risk of extinction (or increase in the expected value of the future) of">	Extinction reduction	</td><td class="mxah"	title="Raising global economic output proportionally by this amount per year">	Global economy	</td><td class="mxah"	title="Increase in income among the world's poorest 2 billion people">	Poorest income	</td><td class="mxah"	title="Saving this many years of healthy life each year">	Healthy years	</td><td class="mxa">	</td><td class="mxah"	title="Pending examples">	Score	</td><td class="mxah"	title="What is the direct annual spending on the problem?">	Annual spending	</td><td class="mxah"	title="What is the number of full time staff working on the problem?">	Staff numbers	</td><td class="mxah"	title="What is the number of active supporters of work on the problem?">	Supporter numbers	</td><td class="mxa">	</td><td class="mxah"	title="Pending examples">	Score	</td><td class="mxah"	title="The doubling of the direct effort described in Neglectedness would be expected to solve this much of the problem defined in Scale">	Solvability	</td>
</tr><tr>	<td class="mxah"	title="Become a millionaire">	0	</td><td class="mxa">		0.0000000%	</td><td class="mxa">		$1,000,000	</td><td class="mxa">		$30,000	</td><td class="mxa">		10	</td><td class="mxa">	</td><td class="mxa">		0	</td><td class="mxa">		> $100,000,000,000	</td><td class="mxa">		 > 1,000,000 	</td><td class="mxa">		> 1,000,000,000 	</td><td class="mxa">	</td><td class="mxa">		0	</td><td class="mxa">		< 0.01%	</td>
</tr><tr>	<td class="mxah"	title="Save 3 lives">	1	</td><td class="mxa">		0.0000001%	</td><td class="mxa">		$10,000,000	</td><td class="mxa">		$300,000	</td><td class="mxa">		100	</td><td class="mxa">	</td><td class="mxa">		1	</td><td class="mxa">		$100,000,000,000	</td><td class="mxa">		 1,000,000 	</td><td class="mxa">		 1,000,000,000 	</td><td class="mxa">	</td><td class="mxa">		1	</td><td class="mxa">		0.01%	</td>
</tr><tr>	<td class="mxah"	title="Turn 10,000 people vegan">	2	</td><td class="mxa">		0.0000010%	</td><td class="mxa">		$100,000,000	</td><td class="mxa">		$3,000,000	</td><td class="mxa">		1,000	</td><td class="mxa">	</td><td class="mxa">		2	</td><td class="mxa">		$10,000,000,000	</td><td class="mxa">		 100,000 	</td><td class="mxa">		 100,000,000 	</td><td class="mxa">	</td><td class="mxa">		2	</td><td class="mxa">		0.10%	</td>
</tr><tr>	<td class="mxah"	title="Remove 5 min/day needless red tape for US teachers">	3	</td><td class="mxa">		0.0000100%	</td><td class="mxa">		$1,000,000,000	</td><td class="mxa">		$30,000,000	</td><td class="mxa">		10,000	</td><td class="mxa">	</td><td class="mxa">		3	</td><td class="mxa">		$1,000,000,000	</td><td class="mxa">		 10,000 	</td><td class="mxa">		 10,000,000 	</td><td class="mxa">	</td><td class="mxa">		3	</td><td class="mxa">		1.00%	</td>
</tr><tr>	<td class="mxah"	title="Identify all risky asteroids">	4	</td><td class="mxa">		0.0001000%	</td><td class="mxa">		$10,000,000,000	</td><td class="mxa">		$300,000,000	</td><td class="mxa">		100,000	</td><td class="mxa">	</td><td class="mxa">		4	</td><td class="mxa">		$100,000,000	</td><td class="mxa">		 1,000 	</td><td class="mxa">		 1,000,000 	</td><td class="mxa">	</td><td class="mxa">		4	</td><td class="mxa">		10.00%	</td>
</tr><tr>	<td class="mxah"	title="Eliminate land use restrictions in major US cities">	5	</td><td class="mxa">		0.0010000%	</td><td class="mxa">		$100,000,000,000	</td><td class="mxa">		$3,000,000,000	</td><td class="mxa">		1,000,000	</td><td class="mxa">	</td><td class="mxa">		5	</td><td class="mxa">		$10,000,000	</td><td class="mxa">		 100 	</td><td class="mxa">		 100,000 	</td><td class="mxa">	</td><td class="mxa">		5	</td><td class="mxa">		100.00%	</td>
</tr><tr>	<td class="mxah"	title="Increase aid by a third and spent it on cash transfers">	6	</td><td class="mxa">		0.0100000%	</td><td class="mxa">		$1,000,000,000,000	</td><td class="mxa">		$30,000,000,000	</td><td class="mxa">		10,000,000	</td><td class="mxa">	</td><td class="mxa">		6	</td><td class="mxa">		$1,000,000	</td><td class="mxa">		 10 	</td><td class="mxa">		 10,000 	</td><td class="mxa">	</td><td class="mxa">		6	</td><td class="mxa">			</td>
</tr><tr>	<td class="mxah"	title="Cure cancer">	7	</td><td class="mxa">		0.1000000%	</td><td class="mxa">		$10,000,000,000,000	</td><td class="mxa">		$300,000,000,000	</td><td class="mxa">		100,000,000	</td><td class="mxa">	</td><td class="mxa">		7	</td><td class="mxa">		$100,000	</td><td class="mxa">		 1 	</td><td class="mxa">		 1,000 	</td><td class="mxa">	</td><td class="mxa">		7	</td><td class="mxa">			</td>
</tr><tr>	<td class="mxah"	title="Eliminate extreme poverty">	8	</td><td class="mxa">		1.0000000%	</td><td class="mxa">		$100,000,000,000,000	</td><td class="mxa">		$3,000,000,000,000	</td><td class="mxa">		1,000,000,000	</td><td class="mxa">	</td><td class="mxa">		8	</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">	</td><td class="mxa">		8	</td><td class="mxa">			</td>
</tr><tr>	<td class="mxah"	title="Eliminate the risk of both nuclear war and pandemics">	9	</td><td class="mxa">		10.0000000%	</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">	</td><td class="mxa">		9	</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">			</td><td class="mxa">	</td><td class="mxa">		9	</td><td class="mxa">			</td>
</tr>
</table>

</div>

<table class="summary" id="pageSummary" style="display: none;">
    <tr>
        <td class="mx">Values</td>
        <td class='mlive mx'></td>
        <td class="mx">Visions</td>
        <td class='vlive mx'></td>
        <td class="mx">Roles</td>
        <td class='olive mx'></td>
        <td class="mx">Goals</td>
        <td class='glive mx'></td>
        <td class="mx">Projects</td>
        <td class='plive mx'></td>
        <td class="mx">Calc gen (s)</td>
        <td class='fgen mx'></td>
        <td class="mx">Page gen (s)</td>
        <td class='pgen mx'>
        <?php
        if(isset($starttime)) {
            $endtime = microtime(true);
            echo round($endtime - $starttime,1);
        }
        ?>
        </td>
        <td class='pgen mx'>Base qry:
        <?php
            echo round($et1 - $starttime,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 2:
        <?php
            echo round($et2,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 3:
        <?php
            echo round($et3,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 4:
        <?php
            echo round($et4,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 5:
        <?php
            echo round($et5,1);
        ?>
        </td>
        <td class='mx' onClick="window.location = 'matrix.php?test=true';">Test</td>
    </tr>
</table>
