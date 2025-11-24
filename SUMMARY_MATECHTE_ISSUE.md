# MATECHTE Sender ID Issue - Summary

## The Problem
Matech (Client ID: 6) is trying to send SMS using sender ID "MATECHTE" through Onfon gateway, but Onfon is rejecting it with:

```
Onfon MessageErrorCode 401: Value filter failed for user [e27847c1-a9fe-4eef-b60d-ddb291b175ab] 
(source_address filter mismatch).
```

## Understanding the Architecture
- **BulkSMS CRM** = Middleman/API Provider
- **Clients** (like Matech) = Use BulkSMS API with their own sender IDs
- **Onfon** = SMS Gateway that BulkSMS uses to actually send messages

## The Issue
Onfon has a **sender ID whitelist/filter** for your account. Only approved sender IDs can be used. "MATECHTE" is not in that approved list.

## Current Approved Sender IDs in Onfon Config:
- FALLEY-MED, MWANGAZACLG, LOGIC-LINK, BriskCredit
- DOFAJA_LTD, FORTRESS, DAKCHES-LTD, MILELE_NKR
- FAVOURLINE, GEOLAND_LTD, FANAKA_FSL, MWANGAIMARA
- AHADI_EPEX, MWEGUNI_LTD, PRADY_TECH, ZEN_PHARMA
- PAGECAPITAL, NKR_A_CLUB, JIRANIHODAR, NOVA_BRIDGE
- MALIK, NOBLE_MICRO, AMPLE_SWISS, NEWPRO_CAP
- DAFACOM_LTD, EMPISAI_LTD, FANUKA_LTD

**MATECHTE is NOT in this list** (even though I added it to config, it needs to be approved in Onfon's system)

## Solution
**Register "MATECHTE" with Onfon Media:**
1. Contact Onfon Media support
2. Request to add "MATECHTE" to your account's approved sender ID list
3. Provide business registration documents if required
4. Once approved, messages will work

## Alternative (Temporary)
Use an already approved sender ID like "PRADY_TECH" for testing until "MATECHTE" is approved.

## Current Status
- Matech channel is configured to use Onfon ✅
- Sender ID "MATECHTE" is set in client record ✅
- Onfon is rejecting because "MATECHTE" not approved ❌

## Next Steps
1. Contact Onfon to register "MATECHTE"
2. OR use approved sender ID temporarily
3. OR switch to Mobitech gateway (if they don't have sender ID restrictions)







