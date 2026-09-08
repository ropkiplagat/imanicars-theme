<?php
/**
 * Kenya export screening rules.
 *
 * The KEBS window is tested as a FORMULA across several calendar years, not as
 * the string "2019". A cutoff hardcoded to 2019 passes a 2019-only test and is
 * silently wrong on 1 January 2027 — and the same class of off-by-one has
 * shipped before from the phrase "8 years or newer".
 */

ic_test( 'KEBS: the cutoff is a formula, and it holds across years' );
ic_is( IC_Salvage_Rules::kebs_cutoff_year( 2026 ), 2019, '2026 clears 2019 or later (the canon statement)' );
ic_is( IC_Salvage_Rules::kebs_cutoff_year( 2027 ), 2020, '2027 clears 2020 or later' );
ic_is( IC_Salvage_Rules::kebs_cutoff_year( 2028 ), 2021, '2028 clears 2021 or later' );
ic_is( IC_Salvage_Rules::kebs_cutoff_year( 2025 ), 2018, '2025 cleared 2018 or later' );

ic_test( 'KEBS: the boundary year is INSIDE the window, not outside it' );
ic_is( IC_Salvage_Rules::kebs_year_ok( 2019, 2026 ), true, '2019 is eligible in 2026 — the boundary case' );
ic_is( IC_Salvage_Rules::kebs_year_ok( 2018, 2026 ), false, '2018 is not' );
ic_is( IC_Salvage_Rules::kebs_year_ok( 2020, 2026 ), true, 'comfortably inside' );
ic_is( IC_Salvage_Rules::kebs_year_ok( 2019, 2027 ), false, 'the same 2019 car is out in 2027' );

ic_test( 'KEBS: an unknown year is unknown, never ineligible' );
ic_is( IC_Salvage_Rules::kebs_year_ok( null, 2026 ), null, 'null year' );
ic_is( IC_Salvage_Rules::kebs_year_ok( '', 2026 ), null, 'empty year' );
ic_is( IC_Salvage_Rules::kebs_year_ok( 1650, 2026 ), null, 'implausible year is unknown, not false' );

ic_test( 'flood: water damage fails PVoC at ANY age' );
ic_is( IC_Salvage_Rules::flood_reject( 'All Over + Water' ), true, 'Water in the damage codes' );
ic_is( IC_Salvage_Rules::flood_reject( 'Front + Water' ), true, 'Water as a secondary code' );
ic_is( IC_Salvage_Rules::flood_reject( 'Flood' ), true, 'the word flood' );
ic_is( IC_Salvage_Rules::flood_reject( 'Submerged' ), true, 'submerged' );
ic_is( IC_Salvage_Rules::flood_reject( 'Front + Front Window' ), false, 'dry damage' );
ic_is( IC_Salvage_Rules::flood_reject( null ), null, 'no damage text is unknown, not clean' );
ic_is( IC_Salvage_Rules::flood_reject( '?' ), null, 'a question mark is unknown, not clean' );

ic_test( 'flood outranks age, and is decided first' );
$k = IC_Salvage_Rules::kenya_eligible( 2025, 'All Over + Water', 2026 );
ic_is( $k['eligible'], false, 'a brand-new flood car is still rejected' );
ic_ok( false !== stripos( implode( ' ', $k['reasons'] ), 'water' ), 'and the reason given is water, not age' );

$k = IC_Salvage_Rules::kenya_eligible( 2008, 'Front + Water', 2026 );
ic_is( $k['eligible'], false, 'an old flood car is rejected too' );
ic_ok( false !== stripos( implode( ' ', $k['reasons'] ), 'water' ), 'water is cited even when age would also disqualify' );

ic_test( 'Kenya eligibility: the three states are all reachable' );
ic_is( IC_Salvage_Rules::kenya_eligible( 2025, 'Theft', 2026 )['eligible'], true, 'new + dry = eligible' );
ic_is( IC_Salvage_Rules::kenya_eligible( 2016, 'Front', 2026 )['eligible'], false, 'old + dry = ineligible' );
ic_is( IC_Salvage_Rules::kenya_eligible( 2025, null, 2026 )['eligible'], null, 'new + damage unpublished = UNKNOWN' );
ic_is( IC_Salvage_Rules::kenya_eligible( null, 'Front', 2026 )['eligible'], null, 'year unknown = UNKNOWN' );

ic_test( 'Kenya eligibility: an unknown never silently becomes a yes' );
$k = IC_Salvage_Rules::kenya_eligible( 2025, null, 2026 );
ic_ok( false !== stripos( implode( ' ', $k['reasons'] ), 'water damage cannot be ruled out' ), 'the unknown is explained, not left blank' );

ic_test( 'VIC statutory write-offs are flagged for the EPA licence' );
ic_is( IC_Salvage_Rules::vic_statutory_epa( 'VIC', 'Statutory Write-Off' ), true, 'VIC + statutory' );
ic_is( IC_Salvage_Rules::vic_statutory_epa( 'VIC', 'Statutory Write-off' ), true, 'case-insensitive on the WOVR wording' );
ic_is( IC_Salvage_Rules::vic_statutory_epa( 'VIC', 'Repairable Write-Off' ), false, 'VIC + repairable is not flagged' );
ic_is( IC_Salvage_Rules::vic_statutory_epa( 'NSW', 'Statutory Write-Off' ), false, 'statutory outside VIC is not flagged' );
ic_is( IC_Salvage_Rules::vic_statutory_epa( null, 'Statutory Write-Off' ), null, 'unknown state is unknown' );

ic_test( 'the EPA note states the licence, and does NOT assert the export position' );
ic_ok( false !== stripos( IC_Salvage_Rules::VIC_EPA_NOTE, 'EPA licence' ), 'names the licence requirement' );
ic_ok( false !== stripos( IC_Salvage_Rules::VIC_EPA_NOTE, 'UNCONFIRMED' ), 'marks the export-vs-dismantling question unconfirmed' );

ic_test( 'state is read out of the location string' );
ic_is( IC_Salvage_Rules::state_from_location( 'Laverton North, VIC' ), 'VIC', 'trailing state' );
ic_is( IC_Salvage_Rules::state_from_location( 'Bohle, Townsville, QLD' ), 'QLD', 'three-part location' );
ic_is( IC_Salvage_Rules::state_from_location( 'SOUTH KEMPSEY, NSW' ), 'NSW', 'uppercase input' );
ic_is( IC_Salvage_Rules::state_from_location( 'Bibra Lake, WA' ), 'WA', 'two-letter state' );
ic_is( IC_Salvage_Rules::state_from_location( 'Somewhere' ), null, 'no state found is null' );
ic_is( IC_Salvage_Rules::state_from_location( null ), null, 'null in, null out' );
ic_is( IC_Salvage_Rules::state_from_location( 'WAGGA' ), null, 'WA inside a word is not a state match' );

ic_test( 'there is no V8 Prado — a row claiming one is flagged as a mis-scrape' );
ic_ok( null !== IC_Salvage_Rules::drivetrain_conflict( 'Prado', '4.5 V8 Turbo Diesel' ), 'V8 Prado flagged' );
ic_ok( null !== IC_Salvage_Rules::drivetrain_conflict( 'Prado', '4.5L' ), '4.5 Prado flagged' );
ic_is( IC_Salvage_Rules::drivetrain_conflict( 'Prado', '2.8 GXL Turbo Diesel' ), null, 'the real 2.8 Prado is fine' );
ic_is( IC_Salvage_Rules::drivetrain_conflict( 'Prado', '4.0 V6 Petrol' ), null, 'the real 4.0 V6 Prado is fine' );
ic_is( IC_Salvage_Rules::drivetrain_conflict( 'LandCruiser', '4.5 V8 Turbo Diesel' ), null, 'the 4.5 V8 belongs to the LandCruiser' );
ic_is( IC_Salvage_Rules::drivetrain_conflict( 'RAV4', '2.5 Hybrid' ), null, 'unrelated models untouched' );
