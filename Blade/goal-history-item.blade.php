<?php
function components($g)
{
    if (isset($g)) {
        $comp = 0;
        // dd($g->questions);
        for ($i = 0; $i < count($g->questions); $i++) {
            if ($g->questions[$i]->answer == 1)
                $comp++;
        }
        return $comp;
    } else
        return 0;
}
?>
<div class="goalhistory-detail">
    @foreach($assessments as $idx=>$assessment)
        <div class="goalhistory-detailleft" id="goalhistory-detailleft">
            <h2>Self-Assessment Completed</h2>
            <h2>{{\Carbon\Carbon::parse($assessment->submit_date)->format('F d,Y | g:ia')}}</h2>
            <hr>
            <h2>GOALS ({{$assessment->goals->count()}})</h2>
            @foreach($assessment->goals as $gidx=> $goal)
                <?php
                $class = 'resourceslibrary-one';
                if ($goal->category->category_name == 'Fitness')
                    $class = "resourceslibrary-one";
                if ($goal->category->category_name == 'Nutrition')
                    $class = "resourceslibrary-two";
                if ($goal->category->category_name == 'Sleep')
                    $class = "resourceslibrary-three";
                if ($goal->category->category_name == 'Sedentary Time')
                    $class = "resourceslibrary-four";
                if ($goal->category->category_name == 'Stress')
                    $class = "resourceslibrary-five";
                ?>
                <ul>
                    <li></li>
                    <li @if($goal->category->category_name=='Fitness')
                        style="color:#F38920" @elseif($goal->category->category_name=='Nutrition') style="color:#739600"
                        @elseif($goal->category->category_name=='Sleep') style="color:#003087;"
                        @elseif($goal->category->category_name=='Sedentary Time') style="color:#FEBE10"
                        @elseif($goal->category->category_name=='Stress') style="color:#007396" @endif><a
                                href="#">{{$goal->category->category_name}}</a></li>
                    <li><a href="#">Components ({{$goal->questions->count() - components($goal)}})</a></li>
                </ul>
            @endforeach
            <hr>
            @if($assessment->goals->count()>0)
            <a href="/portal/goal-products"><i class="fa fa-angle-double-right" aria-hidden="true"></i>Resource Library<br>Recommendations to Help<br>You Achieve This Goal</a>
            @endif
        </div>
        <div class="goalhistory-detailright goalsdetail-right" id="goalhistory-detailright">
            @foreach($assessment->goals as $idx=>$goal)
                <h2>GOAL#:{{$idx+1}}</h2>
                <div class="goalhistory-detailinner{{$idx+1}}">
                    <h2 class="detailinner-title upArrow" @if($goal->category->category_name=='Fitness')
                    style="color:#F38920" @elseif($goal->category->category_name=='Nutrition') style="color:#739600"
                        @elseif($goal->category->category_name=='Sleep') style="color:#003087;"
                        @elseif($goal->category->category_name=='Sedentary Time') style="color:#FEBE10"
                        @elseif($goal->category->category_name=='Stress') style="color:#007396" @endif> {{$goal->category->category_name}}
                        | {{$goal->standard->type}}:</h2>
                    <div class="goalhistory-inner">
                        <h3>Standard {{$goal->standard->index_value}}: {{$goal->standard->standards_name}}.</h3>
                        <h4>COMPONENTS ACHIEVED ({{components($goal)}}/{{$goal->questions->count()}})</h4>
                        <ol>
                            @for($j=0;$j<$goal->questions->count();$j++)
                                <li @if($goal->questions[$j]->answer==0) style="color:#666;font-weight:800;"
                                    @elseif($goal->questions[$j]->answer==1)style="opacity: 0.5" @endif >{{$j+1}}
                                    . {{$goal->questions[$j]->question}}</li><br/>
                            @endfor
                            <div class="clearfix"></div>
                        </ol>
                        <span style="color:#666;font-weight:800;">= Goal Components</span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="clearfix"></div>
    @endforeach
</div>