    <?php
    $standards_dumy = $category->standards;
    $ass_count = \App\assessment::where('user_id', Auth::User()->id)->get();
    $standard_question_count = \App\standard_question::all();
    $type = trim(strtolower($standards[0]->type));
    $answer_verbage = '';
    $color = '';
    switch ($category_name) {
        case 'Fitness':
            $color = '#F38920';
            break;
        case 'Nutrition':
            $color = '#739600';
            break;
        case 'Sleep':
            $color = '#003087';
            break;
        case 'Sedentary Time':
            $color = '#FEBE10';
            break;
        case 'Stress':
            $color = '#007396';
            break;
        default:
            $color = '#F5A939';
    }
    if ($type == 'education') $answer_verbage = "Answer yes or no if your organization provides <strong>education</strong> on the following core components:";
    if ($type == 'programming') $answer_verbage = "Answer yes or no if your organization has <strong>programming</strong> with the following core components:";
    if ($type == 'policy') $answer_verbage = "Answer yes or no if your organization has written <strong>policy</strong> with the following core components:";
    if ($type == 'evaluation') $answer_verbage = "Answer yes or no if your organization implements methods of <strong>evaluation</strong> with the following core components:";
    //footnotes 10/18/16
    $stdid = $standards[0]->id;
    //Organization has written policies regarding nutrition.
    $footnote = '';
    switch ($stdid) {
        case '1':
            $footnote = 'Healthy Fitness Zone is a registered trademark of the Cooper Institute.';
            break;
        case '4':
            $footnote = 'Healthy Fitness Zone is a registered trademark of the Cooper Institute.';
            break;
        case '7':
            $footnote = 'Goldfish is a registered trademark of Pepperidge Farm, Incorporated.';
            break;
    }
    ?>
    @extends('html.master',compact('standards','standards_dumy'))
    @section('content')
        <input type="hidden" value="{{$standards[0]->id}}" id="st_id"/>
        <div id="main-dashboardDetail" class="main-dashboarddetail">
            <div class="sat-goals-popup" id="pop-sat-div">
                <div class="goalspopup-inner">
                        <h2>Please Save Your Responses!</h2>
                        <p>If you continue to the next Standard without saving you will lose all<br>the answers you have provided for this Standard.<br></p>
                    <p>(The "Save" buttton is located at the bottom left of each Standard)</p><p>Are you sure you wish to continue?</p>
                        <ul class="satpopup-list">
                            <input type="hidden" name="_token" value="{{csrf_token()}}" />
                            <li><a id="cancel" href="javascript:void(0)" >No,cancel so I can save my data</a></li>
                            <li><input id="submit" name="name" type="submit" value="Yes,continue and clear my responses"></li>
                        </ul>
                </div>
            </div>
            <div class="allyes-outer">
                <div class="selfassesment-box1">
                    <div class="selfassesmentbox-title" id="accordion">
                        <h2>How to Complete the Self-Assessment </h2>
                        <div class="fitness-Ass-instr">
                            <h3>Introduction.</h3>
                            <p>The Self-Assessment has a section for each of five color-coded topic categories: Fitness,
                                Nutrition, Sedentary Time, Sleep, and Stress. </p>
                            <P>In each category, there are several Standards of Excellence for obesity prevention. These
                                standards relate to Education, Programming, Policy, and Evaluation. When doing the
                                Self-Assessment, you will go through a checklist of Core Components for each Standard. </P>
                            <P>To best answer all items in the Self-Assessment, you may want or need to get input from other
                                staff in your organization. </P>
                            <P>The aim of the Self-Assessment is not to evaluate how well you are achieving the overall
                                mission of your organization. Rather, it focuses on what your organization can do to prevent
                                childhood obesity, in the context of your overall mission. Not all partners aspire to
                                achieve each Standard of Excellence, especially not all-at-once</P>
                            <h3>Instructions.</h3>
                            <P>The self-assessment should take about 60 minutes. You can leave the survey at any time and
                                then return to resume it. Just remember to click Save before you log off. To resume, you
                                will need to log in again. </P>
                            <P>You will be able to submit your responses only when you have completed the entire survey. The
                                site will not allow you to submit an incomplete survey. </P>
                            <P>As you go through the survey, you will see highlighted words in some sections. If you hover
                                the cursor over a highlighted word, you will see a pop-up screen with additional
                                information. </P>

                        </div>
                    </div>
                </div>

                <div class="fitnessoverview-outer">
                    <div class="self-assesmentouter">
                        <div class="selfassesment-title">
                            <h2>SELF-ASSESSMENT STATUS: <b>@if($avg==100) NOT SUBMITTED @else INCOMPLETE @endif</b></h2>
                            <?php Carbon\Carbon::now()->format('m-d-Y');
                            $mytime = Carbon\Carbon::parse($usr_assessment->created_date)->format('m-d-Y');
                            ?>
                            <h4>Started: {{$mytime}}</h4>
                            <div class="clearfix"></div>
                        </div>
                        <div class="assesment-statusbox">
                            <div class="assesment-boxtop">
                                <div class="assesmentbox-left">
                                    <form action="/portal/categories_filter" method="get">
                                        <label>Select Category</label>
                                        <select name="categories" id="nutrition" onchange="this.form.submit()">
                                            @foreach($Cat as $cat)
                                                <option style="color: {{$color}}!important;" value="{{$cat->id}}"
                                                        data-image="/portal/images/{{$cat->category_image}}"
                                                        @if($cat->category_name==$category_name) selected="true" @endif>
                                                    <span style="color: {{$color}}!important;">{{$cat->category_name}}</span>
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <ul class="selectcatagory-list">
                                        @if($standards->currentPage()!= count($standards_dumy))
                                            <li>{{$category_name}}</li>
                                            <li>Standard ({{$standards->currentPage()}}
                                                of @if($staff_flag){{count($standards_dumy)-1}}@else{{count($standards_dumy)-1}}@endif) </li>
                                            <li>{{$standards[0]->type}}</li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="assesmentbox-right">
                                    <span>{{$category_name}} Complete: {{$avg}}%</span>
                                    <span class="top-pagination">{!! $standards->appends(['categories'=>Request::input('categories')])->render() !!}  </span>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <form id="standard-form" action="/portal/SaveAssessmentAnswers" method="POST">
                                <input type="hidden" name="_token" value="{{csrf_token()}}"/>
                                @if($standards->currentPage()!= count($standards_dumy))
                                    <div @if($category_name=='Fitness') class="main-heading"
                                         @elseif($category_name=='Nutrition') class="main-heading main-heading2"
                                         @elseif($category_name=='Sleep') class="main-heading main-heading3"
                                         @elseif($category_name=='Sedentary Time') class="main-heading main-heading4"
                                         @elseif($category_name=='Stress') class="main-heading main-heading5"
                                         @else class="main-heading" @endif>
                                        <h2>Standard {{$standards->currentPage()}}: {{$standards[0]->standards_name}}</h2>
                                        <h3>{!! $answer_verbage !!}</h3>
                                    </div>
                                @else
                                    <div @if($category_name=='Fitness') class="main-heading"
                                         @elseif($category_name=='Nutrition') class="main-heading main-heading2"
                                         @elseif($category_name=='Sleep') class="main-heading main-heading3"
                                         @elseif($category_name=='Sedentary Time') class="main-heading main-heading4"
                                         @elseif($category_name=='Stress') class="main-heading main-heading5"
                                         @else class="main-heading" @endif>
                                        <h3>Adequate training for staff is one of the most important components of success
                                            across all domains. <br>The Fit Kit includes <i>Background and
                                                Recommendations</i> booklets that can be <a href="#">downloaded here</a>.</h3>
                                        <h2>Staff
                                            Training: <?php echo htmlspecialchars_decode(stripslashes($standards[0]->standards_name));?></h2>
                                    </div>
                                @endif
                                <div class="corecomponents-outer">
                                    @if($standards->currentPage()!= count($standards_dumy))
                                        <div class="corecomponents-title">
                                            <h2>CORE COMPONENTS</h2>
                                            <h3>YES</h3>
                                            <h4>NO</h4>
                                        </div>
                                    @endif
                                    @if (count($errors) > 0)
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <?php
                                    $counter = 'option';
                                    $temp_ans_array = null;
                                    $count = 0;
                                    ?>
                                    @if(count($questions)>0)
                                        @foreach ($questions as $qid => $q)
                                            <?php
                                            $size = $questions->count();
                                            $answer = $q->answer;
                                            ?>
                                            <input type="hidden" name="standard_q_id" value="{{$q->standard_id}}">
                                            <input type="hidden" name="q_id{{$qid}}" value="{{$q->id}}">
                                            <input type="hidden" name="size" value="{{$size}}">
                                            @if($standards->currentPage() == count($standards_dumy))
                                                <div class="corecomponents-outer">
                                                    <input type="hidden" name="widget_answer_value" value="-1">
                                                    <input type="hidden" name="staff_training" value="staff">
                                                    <div class="corecomponents-detail fitness-recommendations">
                                                        <div class="corecomponents-radiobutton">
                                                            <ul>
                                                                <li>
                                                                    <input type="radio" id="1" name="option{{$qid}}"
                                                                           value="1" @if($answer=='1')checked="true" @endif>
                                                                    <label for="1">Yes – all staff have read the
                                                                        booklet</label>
                                                                    <div class="check"></div>
                                                                </li>
                                                                <li>
                                                                    <input type="radio" id="2" name="option{{$qid}}"
                                                                           value="2" @if($answer=='2')checked="true" @endif>
                                                                    <label for="2">Somewhat – some staff have read the
                                                                        booklet</label>
                                                                    <div class="check"></div>
                                                                </li>
                                                                <li>
                                                                    <input type="radio" id="3" name="option{{$qid}}"
                                                                           value="0" @if($answer=='0')checked="true" @endif>
                                                                    <label for="3">No – no staff have read the
                                                                        booklet</label>
                                                                    <div class="check"></div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="corecomponents-detail">
                                                <h2><?php echo htmlspecialchars_decode(stripslashes($q->question)); ?></h2>
                                                {{--@if($q->id=='34'||$q->id=='75')--}}
                                                    <div class="corecomponents-radiobutton">
                                                        <ul class="unorder">
                                                            <li>
                                                                <input class="radio_check" type="radio" id="option_{{$qid}}"
                                                                       name="option{{$qid}}" value="1"
                                                                       @if($answer=='1')checked="true" @endif>
                                                                <label for="option_{{$qid}}"></label>
                                                                <div class="check"></div>
                                                            </li>
                                                            <li>
                                                                <input class="radio_check" type="radio" id="{{$q->id}}"
                                                                       name="option{{$qid}}" value="0"
                                                                       @if($answer=='0')checked="true" @endif>
                                                                <label for="{{$q->id}}"></label>
                                                                <div class="check"></div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <?php
                                            $questions2 = DB::select('SELECT * FROM `standard_questions` WHERE standard_questions.standard_id=' . $standards[0]->id);
                                         ?>
                                        @for($ii=0;$ii<count($questions2);$ii++)
                                            <?php
                                            $counter .= $questions2[$ii]->id;
                                            $size = count($questions2);
                                            ?>
                                            <input type="hidden" name="standard_q_id" value="{{$standards[0]->id}}">
                                            <input type="hidden" name="q_id{{$ii}}" value="{{$questions2[$ii]->id}}">
                                            <input type="hidden" name="size" value="{{$size}}">
                                            @if($standards->currentPage() == count($standards_dumy))
                                                <div class="corecomponents-outer">
                                                    <input type="hidden" name="staff_training" value="staff">
                                                    <div class="corecomponents-detail fitness-recommendations">
                                                        <div class="corecomponents-radiobutton">
                                                            <ul>
                                                                <li>
                                                                    <input type="radio" id="1" name="option{{$ii}}"
                                                                           value="1">
                                                                    <label for="1">Yes – all staff have read the
                                                                        booklet</label>
                                                                    <div class="check"></div>
                                                                </li>
                                                                <li>
                                                                    <input type="radio" id="2" name="option{{$ii}}">
                                                                    <label for="2">Somewhat – some staff have read the
                                                                        booklet</label>
                                                                    <div class="check"></div>
                                                                </li>
                                                                <li>
                                                                    <input type="radio" id="3" name="option{{$ii}}" name="option{{$ii}}" value="0">
                                                                    <label for="3">No – no staff have read the
                                                                        booklet</label>
                                                                    <div class="check"></div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="corecomponents-detail">
                                                    <h2>{{$questions2[$ii]->question}}</h2>
                                                    <div class="corecomponents-radiobutton">
                                                        <ul class="unorder">
                                                            <li>
                                                                <input class="radio_check" type="radio" id="{{$counter}}"
                                                                       name="option{{$ii}}" value="1">
                                                                <label for="{{$counter}}"></label>
                                                                <div class="check"></div>
                                                            </li>
                                                            <li>
                                                                <input class="radio_check" type="radio"
                                                                       id="{{$questions2[$ii]->id}}" name="option{{$ii}}"
                                                                       value="0">
                                                                <label for="{{$questions2[$ii]->id}}"></label>
                                                                <div class="check"></div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            @endif
                                        @endfor
                                    @endif
                                </div>
                                @if($standards->currentPage()!= count($standards_dumy))
                                    <div class="trademark-tagline">
                                        {{$footnote}}
                                    </div>
                                    @if(isset($widget_question))
                                        <?php
                                        $record = $widgets_data->where('standard_id', $standards_id)->first();
                                        ?>
                                        <input type="hidden" name="widget_answer_value"
                                               value="@if(!is_null($record)){{$record->widgets_id}}@endif">

                                        @foreach($widget_question as $question_widget)

                                            <div class="organization-detail"
                                                 style="border: 4px solid {{$color}};!important;"
                                                 id="ans_{{$question_widget->id}}">
                                                <div style="text-align: center;">
                                                    <h2>{{$question_widget->question}}</h2>
                                                    <input type="hidden" name="Standards_id" value="{{$standards_id}}">
                                                    <input type="hidden" name="Cat_id" value="{{$cat_id}}">
                                                    <ul>
                                                        <?php
                                                        $widg = $widget->where('widget_question', $question_widget->id);
                                                        ?>
                                                        {{--<input type="hidden"  name="SIZE" value="{{$temp}}">--}}
                                                        @foreach($widg as $wi => $wv)
                                                            <li>
                                                                <div>
                                                                    <input type="radio" class="widget-radio"
                                                                           id="widget_{{$wv->id}}" name="chart_data_"
                                                                           value="{{$wv->id}}"
                                                                           @if(!is_null($record))
                                                                           @if($record->widgets_id === $wv->id) checked="checked" @endif @endif>
                                                                    <div class="check">
                                                                        <div class="inside"></div>
                                                                    </div>
                                                                </div>
                                                                <label for="widget_{{$wv->id}}">
                                                                    <span>{{$wv->value}}</span>
                                                                </label>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="organization-detail" id="not"
                                         style="border: 4px solid {{$color}};!important;">
                                        <div style="text-align: center;">
                                            <h2>Your responses indicate that you have begun to work on the Standard.</h2>
                                            <input type="hidden" name="chart_data" value="60">
                                        </div>
                                    </div>
                                @endif
                                <div class="complete-assesmentouter">
                                    <button class="btn btn-info" name="save" value="save">Save</button>
                                    <span class="Self_Assessment">Self-Assessment Complete: {{$percentComplete}}% </span>
                                    <a class="btn btn-info" href="/portal/CompleteAssesment">Home</a>
                                    <span style="margin-left: 50%"
                                          class="bottom_pagination">{!! $standards->render() !!} </span>
                                    <div class="clearfix"></div>
                                </div>
                                <input type="hidden" name="widget_type" id="widget_type">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('html.footer')
        </div>
    @endsection 
