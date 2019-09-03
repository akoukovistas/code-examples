=== Europadisc Redirects ===
Just in case we need to refer back to the redirect rules, I have added them below:

== Description ==

This will only ever come into effect if it's a 404 URL, otherwise there's no point in checking whether we need to redirect.

Depending on the pre-existing URL structure we can make some assumptions and take actions on the redirect

*/classical/*

If this exists in the target url, it is going to be a product which we will then match based on the ID in the URL is is currently used as the SKU in this site.

*/label/*
If it's an old site url which all end in .htm then apply the redirect rules. Otherwise we can end up in an infinite loop.

Label and sublabel urls are treated the same and will try to be matched to existing terms. If the term does not exist, it will redirect to the label archive.

*more info & feedback*

These will redirect back to the main contact form

*/search/composer/*

No handling for this currently as we have nothing much to map it to.