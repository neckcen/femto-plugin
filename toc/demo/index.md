/*
Title: TOC Plugin Demonstration
Description: A demonstration of the TOC plugin in action.
*/

<style scoped>
/* These should go in your theme. */
.toc {
    border:1px solid #ddd;
    float:right;
    font-size:.9em;
    margin:.5em 0 .5em .5em;
    padding:.3em .5em;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}
.toc ol {
    margin:0 0 0 .5em;
}
.toc > ol {
    margin:0;
}
.toc ol li, .toc ol ol li, .toc ol ol ol li, .toc ol ol ol ol li {
    list-style-type:none;
    margin:0;
}
</style>

{TOC:1,6}

1 is the minimum level for a title to be included and 6 the maximum. Both are
optional so you could just write {TOC} in this case.


Level 1 title
=============
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vehicula condimentum venenatis. Suspendisse eleifend semper hendrerit. Integer a tempus magna. Vivamus lobortis, urna elementum suscipit auctor, sem urna sodales ipsum, sit amet placerat erat erat et turpis. Praesent nulla nisl, fermentum eu arcu non, porttitor condimentum tortor. Nulla vulputate posuere lobortis. Vestibulum leo arcu, pulvinar sed augue ut, convallis suscipit tortor. Vivamus sit amet purus lectus. Phasellus vehicula nunc pulvinar, porta massa ut, euismod nibh.

# Another level 1 title
Ut ipsum mauris, pretium ultricies sem ut, tristique ultrices tellus. Aenean a fermentum justo. Ut pellentesque nisl non feugiat sollicitudin. Ut in placerat enim, ut pretium turpis. Quisque nec neque quam. Suspendisse luctus leo sit amet mauris commodo, semper ornare lectus vestibulum. Donec sagittis rutrum purus, non vehicula massa sagittis sed. Fusce eleifend pretium erat, vitae semper urna aliquet non. Morbi nec odio ut purus lacinia lobortis. Integer eget ligula eget nunc vehicula rutrum. Morbi nec est ligula. In consequat tortor vitae enim pharetra, eget cursus turpis porttitor. Etiam vel lorem pulvinar, malesuada leo id, tristique nunc. Suspendisse interdum diam urna, quis mollis ante pellentesque at.

Level 2 title
-------------
In condimentum pharetra sapien porttitor rutrum. Sed adipiscing odio at turpis lacinia ornare. Proin eros mauris, condimentum in sollicitudin sit amet, bibendum id nulla. Etiam non cursus tortor. Donec sed dictum nisl, nec varius leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. In quis dui id nibh cursus elementum ullamcorper sit amet sapien. Cras sit amet sem at metus malesuada elementum bibendum et purus. Duis tristique lacus eget dolor fringilla fringilla. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris consectetur turpis magna, sed aliquam enim consequat nec. Praesent dictum purus vel orci lacinia venenatis.

## Another level 2 title
Maecenas vel feugiat felis, id tincidunt sem. Cras eleifend dolor sit amet dui scelerisque, vitae consequat arcu blandit. Maecenas cursus magna in sapien dignissim interdum. Quisque dignissim dolor at rhoncus commodo. Proin ac felis lacinia, tristique magna non, congue leo. Pellentesque rhoncus lobortis eros vel imperdiet. Ut suscipit, urna vitae semper cursus, ipsum tellus cursus nibh, id fermentum eros sapien mattis turpis. Curabitur sodales massa quis sapien faucibus accumsan. Duis ac lobortis nisi, adipiscing interdum eros. Nulla eu commodo urna. Praesent non tortor pretium, venenatis lectus sit amet, suscipit magna. Aliquam bibendum lorem ut faucibus vehicula. Suspendisse potenti.

### Level 3 title
Nullam volutpat elit at vestibulum faucibus. Quisque augue ante, fermentum at pharetra ac, tristique ac purus. Suspendisse potenti. Donec tincidunt nibh vel dolor semper, non tempor elit cursus. Sed vulputate suscipit aliquet. Suspendisse ornare quam sit amet scelerisque posuere. Sed euismod pretium erat in laoreet. Vestibulum rhoncus feugiat quam ut congue. Pellentesque non varius ante. Phasellus vehicula laoreet magna, vitae rutrum enim rutrum vitae. Morbi rhoncus leo quam, at pharetra odio adipiscing a. Ut eu augue lacus. Ut in elit ac leo sodales dictum eget ultrices quam. Fusce volutpat nulla at ante blandit, nec auctor arcu gravida. Suspendisse dictum congue felis, ut vulputate neque. Nunc dapibus gravida felis, ac ullamcorper eros euismod vitae.

## Level 2 title again
Maecenas pharetra orci eget feugiat venenatis. Aenean feugiat tellus at mauris egestas rutrum. Phasellus in egestas elit, fermentum vulputate libero. Ut sagittis gravida cursus. Suspendisse pulvinar quam nulla, id hendrerit velit volutpat eget. Nam neque ante, rhoncus in risus quis, pharetra placerat enim. In iaculis feugiat tellus, dictum sagittis velit congue et. Mauris congue nisi ac quam feugiat condimentum. Sed aliquet dapibus ligula a pharetra. Proin vitae pretium turpis. Etiam viverra feugiat velit, a egestas nisi ullamcorper id. Praesent mi leo, facilisis quis auctor eget, posuere non ante. Etiam dapibus mauris non sapien scelerisque, vitae semper ante sagittis. Sed sit amet libero imperdiet, scelerisque tortor a, suscipit neque. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;

#### Level 4 title
Nullam dictum imperdiet ipsum et tristique. Nulla semper leo lectus. Aenean sodales malesuada congue. Nam a massa ac leo euismod bibendum. Sed in dapibus risus. Nullam porttitor libero nisi, a convallis elit rhoncus eget. Nulla dictum id risus blandit ullamcorper. Donec ornare ligula sed enim tristique, nec varius tortor bibendum. Nulla porta vehicula arcu non ornare. Nullam lobortis hendrerit nisi, in tincidunt augue mollis vitae. Morbi dui tortor, tempor dapibus nibh ac, molestie dignissim nulla. Sed hendrerit semper commodo. Integer ultrices leo at felis ultricies porttitor. Aenean facilisis placerat posuere.

##### Level 5 title
Phasellus sit amet tincidunt tellus, eu commodo magna. Vestibulum gravida nulla vitae metus venenatis iaculis. Curabitur bibendum sed velit nec convallis. Nullam sapien elit, convallis ac lorem ac, tincidunt dapibus lacus. Vestibulum dignissim velit scelerisque, porttitor mauris consectetur, consectetur risus. Nulla ac lacus enim. Integer ultricies, lacus quis sollicitudin suscipit, sem massa porttitor odio, id tempor ipsum sem sit amet est. Sed dignissim iaculis tincidunt.

###### Level 6 (max) title
Phasellus laoreet tincidunt pellentesque. Aliquam lobortis imperdiet scelerisque. Maecenas ultrices elit at enim viverra, sed ornare lacus pharetra. In pretium, odio tincidunt bibendum convallis, quam massa dictum magna, ut interdum sem turpis in risus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Praesent convallis elementum suscipit. Sed eget tellus sem. Suspendisse facilisis urna a justo feugiat, ac imperdiet nulla ullamcorper. Cras nec nisl at lectus scelerisque cursus. Integer sagittis ac ante in volutpat. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In a arcu arcu. In porttitor ligula a purus commodo pretium. Cras dolor ante, dictum nec egestas nec, feugiat non dolor.
